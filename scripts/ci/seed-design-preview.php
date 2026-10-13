<?php

/**
 * Browser preview fixture ONLY. Never run against OLING production.
 *
 * Generates enough content to exercise actual resource/sector/expertise Twig
 * templates using Symfony/Doctrine while retaining existing versioned pages.
 *
 * The sample content is intentionally labelled in the preview gallery;
 * it is NOT a production data snapshot and is NEVER imported back.
 */
declare(strict_types=1);

use App\Entity\Metier;
use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Kernel;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$dsn = (string) getenv('DATABASE_URL');
if (
    getenv('OLING_CI_PREVIEW_ONLY') !== '1'
    || !str_contains($dsn, '/oling_preview?')
    || getenv('APP_ENV') !== 'prod'
) {
    fwrite(STDERR, "Refusing fixture seeding outside isolated CI preview.\n");
    exit(2);
}

$kernel = new Kernel('prod', false);
$kernel->boot();
$registry = $kernel->getContainer()->get('doctrine');
$em = $registry->getManager();
$pages = $em->getRepository(SitePage::class);
$now = new DateTimeImmutable();

$ensurePage = static function (
    string $slug,
    string $title,
    string $description,
    string $intro,
    ?string $sideHtml = null
) use ($em, $pages, $now): SitePage {
    $page = $pages->findOneBy(['slug' => $slug]);
    if (!$page instanceof SitePage) {
        $page = (new SitePage())
            ->setSlug($slug)
            ->setTitle($title)
            ->setMetaDescription($description)
            ->setHeroBadge('Ressources OLING')
            ->setHeroTitle($title)
            ->setHeroIntro($intro)
            ->setHeroSideHtml($sideHtml)
            ->setPublicationStatus('published')
            ->setPublishedAt($now)
            ->setPublicationDate($now);
        $em->persist($page);
    }

    if (!$page->getTranslation('fr') instanceof SitePageTranslation) {
        $translation = (new SitePageTranslation())
            ->setSitePage($page)
            ->setSlug($slug)
            ->setLocale('fr')
            ->setTitle($page->getTitle() ?: $title)
            ->setSeoTitle($page->getTitle() ?: $title)
            ->setSeoDescription($page->getMetaDescription() ?: $description)
            ->setHeroBadge($page->getHeroBadge())
            ->setHeroTitle($page->getHeroTitle())
            ->setHeroIntro($page->getHeroIntro())
            ->setHeroSideHtml($page->getHeroSideHtml())
            ->setBodyHtml($page->getBodyHtml())
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt($now);
        $page->addTranslation($translation);
        $em->persist($translation);
    }
    return $page;
};

// Pages created by the versioned public seed may lack their French translation,
// which makes mergeIndexPage/resolveStructuredPage fail in an empty database.
foreach ($pages->findAll() as $page) {
    if ($page instanceof SitePage) {
        $ensurePage(
            (string) $page->getSlug(),
            (string) ($page->getTitle() ?: $page->getSlug()),
            (string) ($page->getMetaDescription() ?: 'Aperçu graphique local OLING'),
            (string) ($page->getHeroIntro() ?: '')
        );
    }
}

$resourceIntro = 'Méthodes, guides et points de vigilance pour sécuriser vos transformations.';
$resourcesHtml = <<<'HTML'
<h2>Expertises recherchées</h2>
<p>Retrouvez les domaines les plus consultés pour qualifier votre besoin et engager un projet.</p>
<ul>
<li><a href="/amoa-si">AMOA SI et pilotage de projets</a></li>
<li><a href="/business-apps/erp">ERP et applications métiers</a></li>
<li><a href="/expertises-audit/rgpd">RGPD et DPO externalisé</a></li>
<li><a href="/cyber-securite">Cybersécurité et résilience</a></li>
<li><a href="/facturation-electronique-amoa">Facturation électronique</a></li>
<li><a href="/dsi-externalisee">Direction des systèmes d'information</a></li>
</ul>
HTML;
$ensurePage(
    'ressources',
    'Ressources pour les transformations SI et la conformité',
    'Ressources méthodologiques : AMOA, ERP, RGPD, cyber et IA.',
    $resourceIntro,
    $resourcesHtml
);

foreach ([
    ['ressource-cadrage-erp', 'Cadrer un projet ERP avant le choix de la solution', 'Processus métiers, scénarios, gouvernance, données et reprise.'],
    ['ressource-rgpd-operationnel', 'Faire du RGPD un dispositif réellement piloté', 'Registres, analyses d’impact, risques et plans d’action.'],
    ['ressource-cyber-gouvernance', 'Construire une gouvernance cyber opérationnelle', 'Responsabilités, référentiels, contrôles et résilience.'],
    ['ressource-transformation-si', 'Piloter une transformation SI complexe', 'Priorités, parties prenantes, budget et gouvernance du projet.'],
] as [$slug, $title, $intro]) {
    $ensurePage($slug, $title, $intro, $intro);
}

foreach ([
    ['transport', 'Transport et logistique'],
    ['eauetassainissement', 'Eau et assainissement'],
    ['banque', 'Banque et assurance'],
    ['collectivites', 'Collectivités territoriales'],
    ['sante', 'Santé et médico-social'],
    ['industrie', 'Industrie'],
    ['formationprofessionnelle', 'Formation professionnelle'],
    ['negoceetdistribution', 'Négoce et distribution'],
    ['mutuelle', 'Mutuelles'],
] as [$slug, $designation]) {
    if (!$em->getRepository(Metier::class)->findOneBy(['slug' => $slug])) {
        $metier = (new Metier())->setSlug($slug)->setDesignation($designation);
        $em->persist($metier);
    }
}

// Use existing versioned French expertise/sector editorial sources in the
// temporary fixture. This creates actual 9-page/3-page hub inventories
// without importing or inventing any public production database records.
foreach ([
    ['expertise_pages.fr.json', 'expertise-', 'expertisePage'],
    ['sector_pages.fr.json', 'secteur-', 'sectorPage'],
] as [$sourceFilename, $prefix, $payloadKey]) {
    $source = json_decode(
        (string) file_get_contents(dirname(__DIR__, 2).'/data/i18n/'.$sourceFilename),
        true,
        512,
        JSON_THROW_ON_ERROR
    );
    if (!is_array($source['pages'] ?? null)) {
        throw new RuntimeException('Invalid isolated-preview source '.$sourceFilename);
    }
    foreach ($source['pages'] as $slug => $payload) {
        $page = $ensurePage(
            $prefix.$slug,
            (string) ($payload['seoTitle'] ?? $payload['title'] ?? $slug),
            (string) ($payload['metaDescription'] ?? $payload['intro'] ?? ''),
            (string) ($payload['intro'] ?? '')
        );
        $translation = $page->getTranslation('fr');
        if ($translation instanceof SitePageTranslation) {
            $translation->setStructuredData([$payloadKey => $payload]);
        }
    }
}

$em->flush();
echo "Isolated design preview: resources, 9 expertise pages, sector pages and sector cards populated in temporary MySQL.\n";
$kernel->shutdown();
