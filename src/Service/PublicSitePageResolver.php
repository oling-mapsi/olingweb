<?php

namespace App\Service;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Entity\Metier;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Repository\MetierRepository;
use App\Repository\SiteGlobalContentRepository;
use App\Repository\SiteGlobalContentTranslationRepository;
use App\Repository\SitePageRepository;

class PublicSitePageResolver
{
    /**
     * Reviewed descriptions from the published EN/ES site-page snapshots.
     * Use these only when a translated CMS page has an empty SEO description.
     * A nonempty published CMS or structured-data value always wins.
     * This does not write into the database or affect the French content.
     */
    private const REVIEWED_INSTITUTIONAL_FALLBACKS = [
        'apropos' => [
            'en' => [
                'seoTitle' => 'About OLING | IT Project Advisory Consultancy',
                'metaDescription' => 'OLING provides IT project advisory, enterprise software and compliance consulting, covering governance, ERP and organisational support.',
                'title' => 'IT project advisory, enterprise software and compliance',
            ],
            'es' => [
                'seoTitle' => 'Quiénes somos | Consultora de proyectos de sistemas de información - OLING',
                'metaDescription' => 'OLING, consultora especializada en proyectos de sistemas de información, software empresarial y cumplimiento normativo. Gobernanza, ERP y acompañamiento a organizaciones.',
                'title' => 'Asesoría en sistemas de información, software empresarial y cumplimiento normativo',
            ],
        ],
        'team' => [
            'en' => [
                'seoTitle' => 'Our Team | IT Project Advisory Experts - OLING',
                'metaDescription' => 'A team of experts in IT project advisory, enterprise software, compliance and cybersecurity to support your strategic projects.',
                'title' => 'A multidisciplinary team of experts',
            ],
            'es' => [
                'seoTitle' => 'El equipo | Expertos en asesoría de sistemas de información - OLING',
                'metaDescription' => 'Un equipo de expertos en asesoría de sistemas de información, software empresarial, cumplimiento y ciberseguridad para acompañar sus proyectos estratégicos.',
                'title' => 'Un equipo de expertos multidisciplinares',
            ],
        ],
    ];

    private static function editorialMetaDescription(
        string $slug,
        string $locale,
        ?string $published,
        ?string $structured,
    ): string {
        $fromCms = trim((string) $published);
        if ($fromCms !== '') {
            return $fromCms;
        }
        $fromStructured = trim((string) $structured);
        if ($fromStructured !== '') {
            return $fromStructured;
        }

        return self::REVIEWED_INSTITUTIONAL_FALLBACKS[$slug][$locale]['metaDescription'] ?? '';
    }

    /**
     * @return array<string, mixed>
     */
    private static function reviewedInstitutionalFallback(string $slug, string $locale): array
    {
        return self::REVIEWED_INSTITUTIONAL_FALLBACKS[$slug][$locale] ?? [];
    }

    private const HOME_PAGE_SLUG = 'home';
    private const EXPERTISE_INDEX_SLUG = 'expertises-index';
    private const SECTOR_INDEX_SLUG = 'secteurs-index';

    private const EXPERTISE_PAGE_SLUGS = [
        'transformation-si-pme-eti' => 'expertise-transformation-si-pme-eti',
        'amoa-erp-applications-metiers' => 'expertise-amoa-erp-applications-metiers',
        'organisation-processus-conduite-du-changement' => 'expertise-organisation-processus-conduite-du-changement',
        'data-automatisation-intelligence-artificielle' => 'expertise-data-automatisation-intelligence-artificielle',
        'cybersecurite-conformite-resilience' => 'expertise-cybersecurite-conformite-resilience',
        'rgpd-dpo-gouvernance' => 'expertise-rgpd-dpo-gouvernance',
        'amoa-ia-pilotage-projets-agents' => 'expertise-amoa-ia-pilotage-projets-agents',
        'conformite-ia-gouvernance-ai-act' => 'expertise-conformite-ia-gouvernance-ai-act',
        'transformation-digitale-ia-pme-pmi' => 'expertise-transformation-digitale-ia-pme-pmi',
    ];

    /**
     * Approved, already-versioned industry photography.
     * Used only when the database entry has neither a hero nor a thumbnail:
     * never overwrite an editorially selected/admin-managed image.
     */
    private const SECTOR_HERO_FALLBACKS = [
        'amenagementethabitats' => '/uploads/metiers/hero/amenagementethabitats-20260809-hero.png',
        'banque' => '/uploads/metiers/hero/banque-20260809-hero.png',
        'cci' => '/uploads/metiers/hero/cci-20260809-hero.png',
        'collectivites' => '/uploads/metiers/hero/collectivites-20260809-hero.png',
        'eauetassainissement' => '/uploads/metiers/hero/eauetassainissement-20260809-hero.png',
        'formationprofessionnelle' => '/uploads/metiers/hero/formationprofessionnelle-20260809-hero.png',
        'industrie' => '/uploads/metiers/hero/industrie-20260809-hero.png',
        'mutuelle' => '/uploads/metiers/hero/mutuelle-20260809-hero.png',
        'negoceetdistribution' => '/uploads/metiers/hero/negoceetdistribution-20260809-hero.png',
        'sante' => '/uploads/metiers/hero/medico-social-20260809-hero.png',
        'transport' => '/uploads/metiers/hero/transport-intermodal-69a3a08f1beb31.17401526.jpg',
    ];

    private const SECTOR_PAGE_SLUGS = [
        'industrie' => 'secteur-industrie',
        'services' => 'secteur-services',
        'secteur-public' => 'secteur-secteur-public',
    ];

    public function __construct(
        private readonly SitePageRepository $sitePageRepository,
        private readonly MetierRepository $metierRepository,
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly SiteGlobalContentRepository $siteGlobalContentRepository,
        private readonly SiteGlobalContentTranslationRepository $siteGlobalContentTranslationRepository,
        private readonly ?PublicMediaResolver $publicMediaResolver = null,
        private readonly ?LocalizedUrlGenerator $localizedUrlGenerator = null,
    ) {
    }

    public function getHomePage(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $defaults = $this->emptyHomePage();
        $sitePage = $this->sitePageRepository->findOneBy(['slug' => self::HOME_PAGE_SLUG]);

        if ($sitePage === null) {
            return $defaults;
        }

        $content = $this->localizedContentResolver->getPublicView($sitePage, $locale);
        if ($content === null) {
            $content = $this->localizedContentResolver->getFrenchPublicView($sitePage);
        }

        $structuredData = $content->getStructuredData();
        $payload = is_array($structuredData['homePage'] ?? null)
            ? $structuredData['homePage']
            : $this->decodeStructuredPayload($content->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);

        $merged['seoTitle'] = $content->getTitle() ?: ($merged['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $content->getMetaDescription() ?: ($merged['metaDescription'] ?? $defaults['metaDescription']);

        if ($content->getHeroBadge() !== null) {
            $merged['hero']['eyebrow'] = trim(strip_tags($content->getHeroBadge()));
        } else {
            $merged['hero']['eyebrow'] = $merged['hero']['eyebrow'] ?? $defaults['hero']['eyebrow'];
        }

        if ($content->getHeroIntro() !== null) {
            $merged['hero']['intro'] = trim(strip_tags($content->getHeroIntro()));
        } else {
            $merged['hero']['intro'] = $merged['hero']['intro'] ?? $defaults['hero']['intro'];
        }

        $titleOverride = $content->getHeroTitle();
        if ($titleOverride !== null) {
            $lines = array_values(array_filter(array_map(
                static fn (string $line): string => trim(strip_tags($line)),
                explode('|', $titleOverride)
            )));
            $merged['hero']['titleLines'] = $lines;
        }

        if ($content->getHeroImage()) {
            $merged['hero']['portraitImage'] = $sitePage->getHeroImage();
        }

        if (str_contains(strtolower((string) ($merged['hero']['badge'] ?? '')), 'iso 27001')) {
            $merged['hero']['badge'] = '';
        }

        $metierSlug = $merged['hero']['metierSlug'] ?? null;
        if ($locale === SitePageTranslation::LOCALE_FR && is_string($metierSlug) && trim($metierSlug) !== '') {
            $metier = $this->metierRepository->findOneBy(['slug' => trim($metierSlug)]);
            if ($metier !== null) {
                $merged['hero']['portraitImage'] = $metier->getImageHero() ?: $metier->getImage() ?: ($merged['hero']['portraitImage'] ?? null);
                $merged['hero']['portraitAlt'] = $metier->getDesignation();
                $merged['hero']['signal']['eyebrow'] = 'Métier adressé';
                $merged['hero']['signal']['title'] = $metier->getDesignation();
                $intro = $this->plainText(is_string($merged['hero']['metierIntro'] ?? null) ? $merged['hero']['metierIntro'] : null);
                if ($intro !== null) {
                    $merged['hero']['signal']['text'] = $intro;
                }
            }
        }

        return $merged;
    }

    private function emptyHomePage(): array
    {
        return [
            'seoTitle' => '',
            'metaDescription' => '',
            'hero' => [
                'eyebrow' => '',
                'titleLines' => [],
                'intro' => '',
                'secondaryCta' => [],
                'tags' => [],
                'portraitImage' => '',
                'portraitAlt' => '',
                'statement' => ['eyebrow' => '', 'title' => '', 'text' => ''],
                'signal' => ['eyebrow' => '', 'title' => '', 'text' => ''],
            ],
            'kpisSection' => ['eyebrow' => '', 'title' => ''],
            'kpis' => [],
            'practices' => ['eyebrow' => '', 'title' => '', 'intro' => '', 'cards' => []],
            'accompaniments' => ['eyebrow' => '', 'title' => '', 'items' => []],
            'proof' => ['eyebrow' => '', 'title' => '', 'items' => [], 'image' => '', 'imageAlt' => ''],
            'projects' => ['eyebrow' => '', 'title' => '', 'intro' => ''],
            'resources' => ['eyebrow' => '', 'title' => '', 'intro' => '', 'cta' => []],
            'finalCta' => ['eyebrow' => '', 'title' => '', 'text' => '', 'primaryCta' => []],
        ];
    }

    public function getEditorialPage(string $slug, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $defaults = [];
        $sitePage = $this->sitePageRepository->findOneBy(['slug' => $slug]);

        if ($sitePage === null) {
            return $defaults;
        }

        $content = $this->localizedContentResolver->getPublicView($sitePage, $locale);
        if ($content === null) {
            return self::reviewedInstitutionalFallback($slug, $locale) ?: $defaults;
        }
        $structuredData = $content->getStructuredData();
        $payload = is_array($structuredData['corePage'] ?? null)
            ? $structuredData['corePage']
            : $this->decodeStructuredPayload($content->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);

        $merged['seoTitle'] = $content->getTitle() ?: ($merged['seoTitle'] ?? '');
        $merged['metaDescription'] = self::editorialMetaDescription($slug, $locale, $content->getMetaDescription(), $merged['metaDescription'] ?? null);
        $merged['eyebrow'] = $content->getHeroBadge() ?: ($merged['eyebrow'] ?? '');
        $merged['title'] = $content->getHeroTitle() ?: ($merged['title'] ?? $content->getTitle() ?: '');
        $merged['intro'] = $this->plainText($content->getHeroIntro()) ?: ($merged['intro'] ?? '');
        $heroMedia = $this->mediaResolver()->resolveSitePageHero($sitePage, $content, $merged['heroImage'] ?? null, $merged['title'] ?? $content->getTitle(), 'Configuration editoriale');
        $merged['heroImage'] = $heroMedia->getImage();
        $merged['heroImageAlt'] = $heroMedia->getAlt();
        $merged['heroImageCaption'] = $heroMedia->getCaption();
        $merged['heroImageFocalPoint'] = $heroMedia->getFocalPoint();
        $merged['heroImageSource'] = $heroMedia->getSource();
        $merged['heroImageDisplayMode'] = $heroMedia->getDisplayMode();
        $merged['heroImageSize'] = $heroMedia->getSize();
        $merged['heroFrame'] = $content->getStructuredData()['heroPresentation']['frame'] ?? 'none';

        if ($content->getHeroSideHtml()) {
            $merged['legacySideHtml'] = $content->getHeroSideHtml();
        }

        if ($content->getBodyHtml() && $payload === []) {
            $merged['legacyBodyHtml'] = $content->getBodyHtml();
        }

        return $merged;
    }

    public function getExpertisesIndex(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $defaults = [
            SitePageTranslation::LOCALE_FR => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler à un consultant'],
                'secondaryCta' => ['route' => 'projets', 'label' => 'Voir des cas clients'],
                'section' => [
                    'kicker' => 'Vos besoins',
                    'title' => 'Choisir les compétences adaptées à votre projet',
                    'text' => 'Nos consultants interviennent sur le cadrage SI, le choix des applications métier, la gouvernance, les données, l’IA et la conformité. Découvrez le contenu des missions et les expertises à mobiliser avec vos équipes.',
                ],
                'cta' => [
                    'title' => 'Besoin d\'un regard indépendant avant de relancer ou sécuriser un sujet critique ?',
                    'text' => 'Nos consultants vous aident à cadrer un projet SI, choisir une solution ou organiser les actions de conformité et leur suivi.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Demander un échange'],
                ],
            ],
            SitePageTranslation::LOCALE_EN => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Talk to a consultant'],
                'secondaryCta' => ['route' => 'projets', 'label' => 'View client cases'],
                'section' => [
                    'kicker' => 'Your needs',
                    'title' => 'Expertise for your IT projects and compliance requirements',
                    'text' => 'Our consultants support IT project scoping, business software selection, governance, data, AI and compliance. Explore the scope of our assignments and the expertise available to your teams.',
                ],
                'cta' => [
                    'title' => 'Need an independent view before restarting or securing a critical topic?',
                    'text' => 'OLING steps in when an ERP project, an IT roadmap, a compliance risk or an AI topic needs to become steerable again quickly.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Request a discussion'],
                ],
            ],
            SitePageTranslation::LOCALE_ES => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Hablar con un consultor'],
                'secondaryCta' => ['route' => 'projets', 'label' => 'Ver casos de cliente'],
                'section' => [
                    'kicker' => 'Sus necesidades',
                    'title' => 'Especialidades para sus proyectos SI y su cumplimiento normativo',
                    'text' => 'Nuestros consultores acompañan la definición de proyectos SI, la selección de software, la gobernanza, los datos, la IA y el cumplimiento normativo.',
                ],
                'cta' => [
                    'title' => 'Necesita una mirada independiente antes de relanzar o asegurar un tema crítico?',
                    'text' => 'OLING interviene cuando un proyecto ERP, una trayectoria SI, un riesgo de cumplimiento o un tema de IA deben volver a ser pilotables rápidamente.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Solicitar una conversación'],
                ],
            ],
        ];

        return $this->mergeIndexPage(
            $this->sitePageRepository->findOneBy(['slug' => self::EXPERTISE_INDEX_SLUG]),
            array_merge([
                'seoTitle' => 'Expertises | OLING',
                'metaDescription' => 'Découvrez les expertises OLING pour les PME, PMI et ETI : transformation SI, AMOA ERP, organisation, data, IA, cybersécurité et conformité.',
                'eyebrow' => 'Expertises OLING',
                'title' => 'AMOA SI, applications métiers, gouvernance DSI, risques et conformité',
                'intro' => 'OLING structure son accompagnement autour des sujets qui exposent directement la direction : arbitrages SI, projets ERP, conformité, gouvernance des données, résilience et usages IA.',
                'highlights' => ['AMO ERP et applications métiers', 'Risques, cyber et résilience', 'RGPD, gouvernance et IA'],
            ], $defaults[$locale]),
            $locale
        );
    }

    public function getExpertisePages(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $pages = [];

        foreach (self::EXPERTISE_PAGE_SLUGS as $slug => $sitePageSlug) {
            $sitePage = $this->sitePageRepository->findOneBy(['slug' => $sitePageSlug]);
            $page = $this->resolveStructuredPage($sitePage, 'expertisePage', $locale);
            if ($page !== null) {
                $page['publicSlug'] = $slug;
                $page['sourceSlug'] = $sitePageSlug;
                $page['href'] = $sitePage instanceof SitePage ? $this->localizedUrlGenerator?->sitePagePath($sitePage, $locale) : null;
                $pages[$slug] = $page;
            }
        }

        return $pages;
    }

    public function getSectorsIndex(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $defaults = [
            SitePageTranslation::LOCALE_FR => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Échanger sur votre contexte'],
                'secondaryCta' => ['route' => 'expertises_index', 'label' => 'Voir nos expertises'],
                'section' => [
                    'kicker' => 'Vue d’ensemble',
                    'title' => 'Comprendre votre activité pour accompagner vos projets',
                    'text' => 'Les enjeux de production, de service public, de relation client et de conformité appellent des réponses différentes. Nos consultants associent la connaissance de votre secteur à leurs compétences en AMOA SI, progiciels et gouvernance.',
                ],
                'cta' => [
                    'title' => 'Besoin d’un cadrage adapté à votre secteur',
                    'text' => 'OLING peut intervenir pour qualifier les priorités, sécuriser les choix d’outillage et structurer le pilotage selon votre environnement.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Parler de votre contexte'],
                ],
            ],
            SitePageTranslation::LOCALE_EN => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Discuss your context'],
                'secondaryCta' => ['route' => 'expertises_index', 'label' => 'View our expertise'],
                'section' => [
                    'kicker' => 'Overview',
                    'title' => 'A simple view of the sectors actually covered',
                    'text' => 'Production, public services, customer relationships and compliance call for different approaches. OLING combines knowledge of your industry with IT project advisory, business software and governance expertise.',
                ],
                'cta' => [
                    'title' => 'Need framing adapted to your sector?',
                    'text' => 'OLING can help qualify priorities, secure tooling choices and structure steering according to your environment.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Discuss your context'],
                ],
            ],
            SitePageTranslation::LOCALE_ES => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Hablar de su contexto'],
                'secondaryCta' => ['route' => 'expertises_index', 'label' => 'Ver nuestras especializaciones'],
                'section' => [
                    'kicker' => 'Vista general',
                    'title' => 'Una lectura simple de los sectores realmente cubiertos',
                    'text' => 'La producción, los servicios públicos, la relación con los clientes y el cumplimiento normativo requieren enfoques diferentes. OLING combina el conocimiento de su sector con la asesoría en proyectos SI, software empresarial y gobernanza.',
                ],
                'cta' => [
                    'title' => 'Necesita un encuadre adaptado a su sector?',
                    'text' => 'OLING puede intervenir para calificar prioridades, asegurar decisiones de herramientas y estructurar el pilotaje según su entorno.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Hablar de su contexto'],
                ],
            ],
        ];

        return $this->mergeIndexPage(
            $this->sitePageRepository->findOneBy(['slug' => self::SECTOR_INDEX_SLUG]),
            array_merge([
                'seoTitle' => 'Secteurs | OLING',
                'metaDescription' => 'Découvrez l’ensemble des secteurs servis par OLING et les points de vigilance connus selon chaque environnement métier.',
                'eyebrow' => 'Nos secteurs',
                'title' => 'Les secteurs métiers accompagnés par OLING',
                'intro' => 'OLING intervient dans des environnements très différents, avec une même logique : comprendre les contraintes opérationnelles, clarifier les priorités et sécuriser les transformations utiles.',
                'highlights' => ['Organisations publiques et privées', 'Missions ponctuelles et grands programmes', 'AMOA SI, progiciels et conformité'],
            ], $defaults[$locale]),
            $locale
        );
    }

    public function getSectorPages(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $pages = [];

        foreach (self::SECTOR_PAGE_SLUGS as $slug => $sitePageSlug) {
            $sitePage = $this->sitePageRepository->findOneBy(['slug' => $sitePageSlug]);
            $page = $this->resolveStructuredPage($sitePage, 'sectorPage', $locale);
            if ($page !== null) {
                $page['publicSlug'] = $slug;
                $page['sourceSlug'] = $sitePageSlug;
                $page['href'] = $sitePage instanceof SitePage ? $this->localizedUrlGenerator?->sitePagePath($sitePage, $locale) : null;
                $pages[$slug] = $page;
            }
        }

        return $pages;
    }

    public function expertisePublicSlugForSource(string $sourceSlug): ?string
    {
        $slug = array_search($sourceSlug, self::EXPERTISE_PAGE_SLUGS, true);

        return is_string($slug) ? $slug : null;
    }

    public function sectorPublicSlugForSource(string $sourceSlug): ?string
    {
        $slug = array_search($sourceSlug, self::SECTOR_PAGE_SLUGS, true);

        return is_string($slug) ? $slug : null;
    }

    public function isExpertisesIndexSource(string $sourceSlug): bool
    {
        return $sourceSlug === self::EXPERTISE_INDEX_SLUG;
    }

    public function isSectorsIndexSource(string $sourceSlug): bool
    {
        return $sourceSlug === self::SECTOR_INDEX_SLUG;
    }

    public function getPracticeNarrative(\App\Entity\Practice $practice): array
    {
        $native = $this->localizedContentResolver->getFrenchPracticeView($practice)->getPublicNarrative();
        // Only these two practices are part of this editorial integration.
        if (in_array($practice->getSlug(), ['consulting', 'mapsi'], true) && is_array($native) && $native !== []) { return $native; }
        $slug = (string) $practice->getSlug();
        if ($slug === '') {
            return [];
        }

        $content = $this->siteGlobalContentRepository->findOneBy(['identifier' => 'practice_narrative_' . $slug]);
        if ($content === null || !$content->isEnabled()) {
            return [];
        }

        $translation = $this->siteGlobalContentTranslationRepository->findOneBy([
            'siteGlobalContent' => $content,
            'locale' => \App\Entity\SitePageTranslation::LOCALE_FR,
        ]);
        if ($translation === null) {
            return [];
        }

        return $this->decodeStructuredPayload($translation->getBodyHtml());
    }

    /**
     * @return Metier[]
     */
    public function getPublishedMetiers(): array
    {
        return $this->metierRepository->findBy([], ['designation' => 'ASC']);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getSectorCatalogEntries(string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $entries = [];

        $missions = $locale === SitePageTranslation::LOCALE_FR ? ($this->getEditorialPage('projets', $locale)['proofLibrary']['missions'] ?? []) : [];
        foreach ($this->getPublishedMetiers() as $metier) {
            $slug = (string) $metier->getSlug();
            $designation = $this->localizedSectorDesignation($slug, trim((string) $metier->getDesignation()), $locale);
            $summary = $this->getSectorSummary($slug, $designation, $locale);
            $projectCount = $metier->getProjets()->count();
            $references = [];
            $sectorNeedle = match ($slug) {
                'industrie' => 'industrie', 'eauetassainissement' => 'eau et assainissement',
                'transport' => 'transport', 'formationprofessionnelle' => 'formation',
                'collectivités', 'collectivites' => 'public local', 'mutuelle' => 'assurance',
                'amenagementethabitats' => 'immobilier', default => null,
            };
            foreach ($missions as $mission) {
                if ($sectorNeedle !== null && str_contains(mb_strtolower((string) ($mission['sector'] ?? '')), $sectorNeedle) && !empty($mission['id']) && !empty($mission['title'])) {
                    $references[] = ['href' => '/projets#'.rawurlencode($mission['id']), 'title' => $mission['title']];
                    break;
                }
            }

            $entries[] = [
                'slug' => $slug,
                'designation' => $designation,
                'intro' => $summary['intro'],
                'specifics' => $summary['specifics'],
                'projectCount' => $projectCount,
                'references' => $references,
                'heroImage' => $metier->getImageHero() ?: $metier->getImage() ?: (self::SECTOR_HERO_FALLBACKS[$slug] ?? null),
                'detailUrl' => $this->resolveSectorDetailUrl($slug, $locale),
            ];
        }

        return $entries;
    }

    private function mergeIndexPage(?SitePage $sitePage, array $defaults, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        if ($sitePage === null) {
            return $defaults;
        }

        $content = $this->localizedContentResolver->getPublicView($sitePage, $locale)
            ?? $this->localizedContentResolver->getFrenchPublicView($sitePage);
        $payload = $this->decodeStructuredPayload($content->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);
        $merged['seoTitle'] = $content->getTitle() ?: ($merged['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $content->getMetaDescription() ?: ($merged['metaDescription'] ?? $defaults['metaDescription']);
        $merged['eyebrow'] = $content->getHeroBadge() ?: ($merged['eyebrow'] ?? $defaults['eyebrow']);
        $merged['title'] = $content->getHeroTitle() ?: ($merged['title'] ?? $defaults['title']);
        $merged['intro'] = $this->plainText($content->getHeroIntro()) ?: ($merged['intro'] ?? $defaults['intro']);

        $heroMedia = $this->mediaResolver()->resolveSitePageHero($sitePage, $content, $payload['heroImage'] ?? null, $merged['title'], 'Configuration du hub');
        $merged['heroImage'] = $heroMedia->getImage();
        $merged['heroImageAlt'] = $heroMedia->getAlt();
        $merged['heroImageCaption'] = $heroMedia->getCaption();
        $merged['heroImageFocalPoint'] = $heroMedia->getFocalPoint();
        $merged['heroImageDisplayMode'] = $heroMedia->getDisplayMode();
        $merged['heroImageSize'] = $heroMedia->getSize();
        $merged['heroFrame'] = $content->getStructuredData()['heroPresentation']['frame'] ?? 'none';

        if (array_key_exists('highlights', $defaults)) {
            $merged['highlights'] = $this->normalizeStringList($payload['highlights'] ?? null, $merged['highlights'] ?? $defaults['highlights']);
        }

        return $merged;
    }

    private function mergeStructuredPage(?SitePage $sitePage, array $defaults): array
    {
        if ($sitePage === null) {
            return $defaults;
        }

        $content = $this->localizedContentResolver->getFrenchPublicView($sitePage);
        $payload = $this->decodeStructuredPayload($content->getBodyHtml());

        $merged = $defaults;
        $merged['title'] = $content->getHeroTitle() ?: ($payload['title'] ?? $defaults['title']);
        $merged['seoTitle'] = $content->getTitle() ?: ($payload['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $content->getMetaDescription() ?: ($payload['metaDescription'] ?? $defaults['metaDescription']);
        $merged['eyebrow'] = $content->getHeroBadge() ?: ($payload['eyebrow'] ?? $defaults['eyebrow']);
        $merged['intro'] = $this->plainText($content->getHeroIntro()) ?: ($payload['intro'] ?? $defaults['intro']);
        $merged['heroImage'] = $sitePage->getHeroImage() ?: ($payload['heroImage'] ?? ($defaults['heroImage'] ?? null));

        foreach (['situations', 'interventions', 'deliverables', 'issues', 'linkedExpertises'] as $field) {
            if (array_key_exists($field, $defaults)) {
                $merged[$field] = $this->normalizeStringList($payload[$field] ?? null, $defaults[$field]);
            }
        }

        if (array_key_exists('linkedServices', $defaults) && isset($payload['linkedServices']) && is_array($payload['linkedServices'])) {
            $linkedServices = array_values(array_filter($payload['linkedServices'], static fn ($item): bool => is_array($item) && isset($item['practice'], $item['service'])));
            if ($linkedServices !== []) {
                $merged['linkedServices'] = $linkedServices;
            }
        }

        return $merged;
    }

    private function resolveStructuredPage(?SitePage $sitePage, string $payloadKey, string $locale = SitePageTranslation::LOCALE_FR): ?array
    {
        if ($sitePage === null) {
            return null;
        }

        $content = $this->localizedContentResolver->getPublicView($sitePage, $locale)
            ?? $this->localizedContentResolver->getFrenchPublicView($sitePage);
        $structuredData = $content->getStructuredData();
        $payload = is_array($structuredData[$payloadKey] ?? null)
            ? $structuredData[$payloadKey]
            : $this->decodeStructuredPayload($content->getBodyHtml());
        if ($payload === []) {
            return null;
        }

        $payload['title'] = $content->getHeroTitle() ?: ($payload['title'] ?? '');
        $payload['seoTitle'] = $content->getTitle() ?: ($payload['seoTitle'] ?? $payload['title']);
        $payload['metaDescription'] = $content->getMetaDescription() ?: ($payload['metaDescription'] ?? '');
        $payload['eyebrow'] = $content->getHeroBadge() ?: ($payload['eyebrow'] ?? '');
        $payload['intro'] = $this->plainText($content->getHeroIntro()) ?: ($payload['intro'] ?? '');
        $heroMedia = $this->mediaResolver()->resolveSitePageHero($sitePage, $content, $payload['heroImage'] ?? null, $payload['title'] ?? $content->getTitle(), 'Payload structure');
        $payload['heroImage'] = $heroMedia->getImage();
        $payload['heroImageAlt'] = $heroMedia->getAlt();
        $payload['heroImageCaption'] = $heroMedia->getCaption();
        $payload['heroImageFocalPoint'] = $heroMedia->getFocalPoint();
        $payload['heroImageSource'] = $heroMedia->getSource();
        $payload['heroImageDisplayMode'] = $heroMedia->getDisplayMode();
        $payload['heroImageSize'] = $heroMedia->getSize();

        return $payload;
    }

    private function decodeStructuredPayload(?string $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function mediaResolver(): PublicMediaResolver
    {
        return $this->publicMediaResolver ?? new PublicMediaResolver();
    }

    /**
     * @param mixed $values
     * @param array<int, string> $fallback
     * @return array<int, string>
     */
    private function normalizeStringList(mixed $values, array $fallback): array
    {
        if (!is_array($values)) {
            return $fallback;
        }

        $normalized = array_values(array_filter(array_map(
            static fn ($item): string => is_scalar($item) ? trim(strip_tags((string) $item)) : '',
            $values
        )));

        return $normalized !== [] ? $normalized : $fallback;
    }

    private function plainText(?string $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $text = trim(strip_tags($value));

        return $text !== '' ? $text : null;
    }

    private function mergeRecursive(array $defaults, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if (is_array($value) && isset($defaults[$key]) && is_array($defaults[$key]) && !$this->isList($value) && !$this->isList($defaults[$key])) {
                $defaults[$key] = $this->mergeRecursive($defaults[$key], $value);
                continue;
            }

            $defaults[$key] = $value;
        }

        return $defaults;
    }

    private function isList(array $value): bool
    {
        if (function_exists('array_is_list')) {
            return array_is_list($value);
        }

        return array_keys($value) === range(0, count($value) - 1);
    }

    /**
     * @return array{intro: string, specifics: array<int, string>}
     */
    private function localizedSectorDesignation(string $slug, string $fallback, string $locale): string
    {
        return [
            SitePageTranslation::LOCALE_EN => [
                'amenagementethabitats' => 'Planning, housing and asset management',
                'banque' => 'Banking',
                'cci' => 'Chambers of commerce',
                'collectivités' => 'Local authorities',
                'collectivites' => 'Local authorities',
                'eauetassainissement' => 'Water and wastewater',
                'formationprofessionnelle' => 'Vocational training',
                'industrie' => 'Industry',
                'mutuelle' => 'Mutual insurance and insurance',
                'negoceetdistribution' => 'Wholesale and distribution',
                'sante' => 'Healthcare',
                'transport' => 'Transport',
            ],
            SitePageTranslation::LOCALE_ES => [
                'amenagementethabitats' => 'Ordenación territorial, vivienda y patrimonio',
                'banque' => 'Banca',
                'cci' => 'Cámaras de comercio',
                'collectivités' => 'Administraciones locales',
                'collectivites' => 'Administraciones locales',
                'eauetassainissement' => 'Agua y saneamiento',
                'formationprofessionnelle' => 'Formación profesional',
                'industrie' => 'Industria',
                'mutuelle' => 'Mutuas y seguros',
                'negoceetdistribution' => 'Comercio y distribución',
                'sante' => 'Salud',
                'transport' => 'Transporte',
            ],
        ][$locale][$slug] ?? $fallback;
    }

    private function getSectorSummary(string $slug, string $designation, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        if ($locale === SitePageTranslation::LOCALE_EN) {
            return $this->getEnglishSectorSummary($slug, $designation);
        }
        if ($locale === SitePageTranslation::LOCALE_ES) {
            return $this->getSpanishSectorSummary($slug, $designation);
        }

        return match ($slug) {
            'amenagementethabitats' => [
                'intro' => 'Bailleurs sociaux et aménageurs doivent coordonner la gestion du patrimoine, leurs outils et les acteurs de leurs projets. OLING les accompagne dans la gouvernance SI et l’organisation de leurs transformations.',
                'specifics' => ['Gouvernance SI et coordination des projets', 'Organisation et pilotage des systèmes d’information'],
            ],
            'banque' => [
                'intro' => 'La disponibilité des infrastructures et la continuité des services sont essentielles aux activités bancaires. Les missions OLING portent notamment sur les datacenters, la virtualisation et les plans de transformation SI.',
                'specifics' => ['Pilotage des évolutions d’infrastructure', 'PCA/PRA et planification des transformations'],
            ],
            'cci' => [
                'intro' => 'Les chambres consulaires font évoluer leurs services aux entreprises et leurs outils de formation. OLING les accompagne sur les processus, les ERP, le schéma directeur SI et la conformité RGPD.',
                'specifics' => ['Modernisation des outils de gestion et des services numériques', 'AMOA SI et protection des données'],
            ],
            'collectivites', 'collectivités' => [
                'intro' => 'Les collectivités doivent moderniser leurs services numériques tout en organisant la mutualisation et le suivi des projets publics. OLING intervient en AMOA SI, gouvernance et RGPD/DPO.',
                'specifics' => ['Schémas directeurs SI et catalogues de services', 'DPO externalisé et gouvernance des services mutualisés'],
            ],
            'eauetassainissement' => [
                'intro' => 'Les opérateurs de l’eau doivent relier les interventions sur le réseau aux données techniques, clients et financières. OLING accompagne la convergence SI et les projets de GMAO en lien avec le SIG et l’exploitation.',
                'specifics' => ['GMAO, SI interventions et échanges avec les SI Clients', 'Interfaces avec le SIG, les RH et le SI Finance'],
            ],
            'formationprofessionnelle' => [
                'intro' => 'Organismes de formation et CFA doivent suivre leurs processus et leurs obligations qualité dans la durée. OLING intervient en direction qualité déléguée, QSE, Qualiopi et conformité RGPD.',
                'specifics' => ['Pilotage qualité et suivi des indicateurs Qualiopi', 'Organisation, documentation et outils de gestion'],
            ],
            'industrie' => [
                'intro' => 'Un projet ERP industriel engage la production, les flux et les équipes métier. OLING accompagne le cadrage, le choix des progiciels et le pilotage des projets ERP, CRM et GMAO.',
                'specifics' => ['AMOA ERP et coordination avec la production et la maintenance', 'Gouvernance SI, intérim DSI et conformité RGPD'],
            ],
            'mutuelle' => [
                'intro' => 'Les mutuelles et les assureurs doivent protéger leurs données et assurer la continuité de leurs activités. OLING les accompagne sur l’analyse d’impact, le PCA/PRA, les exigences DORA et le pilotage SI.',
                'specifics' => ['Analyse d’impact métier et plans de continuité', 'Gouvernance, données et contrôle de gestion'],
            ],
            'negoceetdistribution' => [
                'intro' => 'Achats, stocks, ventes et comptabilité doivent s’appuyer sur des outils cohérents. OLING accompagne les acteurs du négoce et de la distribution dans leurs projets ERP et l’organisation de leur fonction SI.',
                'specifics' => ['ERP, gestion commerciale et processus financiers', 'Outils collaboratifs Microsoft 365 et pilotage SI'],
            ],
            'sante' => [
                'intro' => 'La fiabilité des données et la coordination des acteurs sont centrales dans les projets de santé. OLING intervient notamment sur les SI métier, les ERP et le pilotage de programmes liés à la vaccination ou à la répartition pharmaceutique.',
                'specifics' => ['AMOA des progiciels et coordination des projets SI', 'Continuité de service et qualité des données'],
            ],
            'transport' => [
                'intro' => 'Ports, aéroports et opérateurs de transport doivent coordonner exploitation, fonctions support et obligations réglementaires. OLING intervient sur le SI Finance, les ERP, la gouvernance, le QSE et le RGPD.',
                'specifics' => ['AMOA SI Finance, paie, GRH et schémas directeurs', 'QSE, cybersécurité, RGPD et continuité d’activité'],
            ],
            default => ['intro' => 'OLING accompagne les projets SI et les démarches de conformité de votre organisation.', 'specifics' => []],
        };
    }

    private function getEnglishSectorSummary(string $slug, string $designation): array
    {
        return match ($slug) {
            'amenagementethabitats' => ['intro' => 'OLING supports housing providers and property development organisations with IT governance and project coordination.', 'specifics' => ['IT governance', 'Project organisation and coordination']],
            'banque' => ['intro' => 'Banking IT projects require careful planning of infrastructure changes and service continuity. OLING supports datacentre, virtualisation and IT roadmap work.', 'specifics' => ['Infrastructure project management', 'Business continuity and transformation planning']],
            'cci' => ['intro' => 'Chambers of commerce are modernising services to businesses and training organisations. OLING supports ERP, IT roadmap and GDPR projects.', 'specifics' => ['Business software and digital services', 'IT project advisory and data protection']],
            'collectivites', 'collectivités' => ['intro' => 'Local authorities need to modernise digital services while coordinating shared IT and public projects. OLING supports IT project advisory, governance and GDPR/DPO work.', 'specifics' => ['IT roadmaps and service catalogues', 'Outsourced DPO and shared-service governance']],
            'eauetassainissement' => ['intro' => 'Water operators must connect field interventions with technical, customer and finance data. OLING supports IT convergence and CMMS projects linked to GIS and operations.', 'specifics' => ['CMMS, field interventions and customer systems', 'GIS, HR and finance interfaces']],
            'formationprofessionnelle' => ['intro' => 'Training providers and apprenticeship centres need to maintain quality processes and evidence. OLING supports outsourced quality management, QHSE, Qualiopi and GDPR work.', 'specifics' => ['Quality management and Qualiopi indicators', 'Organisation, documentation and management tools']],
            'industrie' => ['intro' => 'Industrial ERP projects involve production, processes and business teams. OLING supports ERP, CRM and CMMS scoping, software selection and project management.', 'specifics' => ['ERP advisory and production coordination', 'IT governance, outsourced CIO support and GDPR']],
            'mutuelle' => ['intro' => 'Mutual insurers and insurance organisations must protect data and maintain business continuity. OLING supports impact analysis, continuity planning and IT governance.', 'specifics' => ['Business impact analysis and continuity plans', 'Governance, data and management control']],
            'negoceetdistribution' => ['intro' => 'Wholesale and distribution businesses need consistent purchasing, stock, sales and accounting tools. OLING supports ERP projects and the organisation of the IT function.', 'specifics' => ['ERP, sales management and finance processes', 'Microsoft 365 and IT management']],
            'sante' => ['intro' => 'Healthcare IT projects depend on reliable data and coordination between stakeholders. OLING supports business systems, ERP and programme management.', 'specifics' => ['Business software advisory and IT project coordination', 'Service continuity and data quality']],
            'transport' => ['intro' => 'Ports, airports and transport operators must coordinate operations, support functions and regulatory obligations. OLING supports finance systems, ERP, QHSE and GDPR work.', 'specifics' => ['Finance ERP, HR and IT roadmaps', 'QHSE, cybersecurity, GDPR and continuity']],
            default => ['intro' => 'Discuss your IT project and compliance requirements with OLING.', 'specifics' => []],
        };
    }

    private function getSpanishSectorSummary(string $slug, string $designation): array
    {
        return match ($slug) {
            'amenagementethabitats' => ['intro' => 'OLING acompaña a entidades de vivienda y urbanismo en la gobernanza SI y la coordinación de sus proyectos.', 'specifics' => ['Gobernanza SI', 'Organización y coordinación de proyectos']],
            'banque' => ['intro' => 'Los proyectos SI bancarios requieren planificar la evolución de las infraestructuras y la continuidad del servicio. OLING acompaña los trabajos de centros de datos, virtualización y planificación SI.', 'specifics' => ['Dirección de proyectos de infraestructura', 'Continuidad y planificación de las transformaciones']],
            'cci' => ['intro' => 'Las cámaras de comercio modernizan sus servicios a empresas y organismos de formación. OLING acompaña proyectos ERP, planes directores SI y RGPD.', 'specifics' => ['Software de gestión y servicios digitales', 'Asesoría SI y protección de datos']],
            'collectivites', 'collectivités' => ['intro' => 'Las administraciones locales deben modernizar sus servicios digitales y coordinar los servicios SI compartidos. OLING acompaña proyectos SI, gobernanza y trabajos RGPD/DPO.', 'specifics' => ['Planes directores SI y catálogos de servicios', 'DPO externo y gobernanza de servicios compartidos']],
            'eauetassainissement' => ['intro' => 'Los operadores de agua deben relacionar las intervenciones con los datos técnicos, de clientes y financieros. OLING acompaña la convergencia SI y los proyectos GMAO vinculados al SIG y a la explotación.', 'specifics' => ['GMAO, intervenciones y sistemas de clientes', 'Interfaces con SIG, RR. HH. y finanzas']],
            'formationprofessionnelle' => ['intro' => 'Los organismos de formación y los centros de aprendizaje deben mantener sus procesos y evidencias de calidad. OLING acompaña la dirección de calidad, QSE, Qualiopi y RGPD.', 'specifics' => ['Gestión de calidad e indicadores Qualiopi', 'Organización, documentación y herramientas de gestión']],
            'industrie' => ['intro' => 'Los proyectos ERP industriales afectan a la producción, los procesos y los equipos de negocio. OLING acompaña la definición, la selección y la dirección de proyectos ERP, CRM y GMAO.', 'specifics' => ['Asesoría ERP y coordinación con producción', 'Gobernanza SI, dirección SI externa y RGPD']],
            'mutuelle' => ['intro' => 'Las mutuas y aseguradoras deben proteger los datos y mantener la continuidad de sus actividades. OLING acompaña el análisis de impacto, los planes de continuidad y la gobernanza SI.', 'specifics' => ['Análisis de impacto y planes de continuidad', 'Gobernanza, datos y control de gestión']],
            'negoceetdistribution' => ['intro' => 'Las empresas de distribución necesitan herramientas coherentes para compras, existencias, ventas y contabilidad. OLING acompaña los proyectos ERP y la organización de la función SI.', 'specifics' => ['ERP, gestión comercial y procesos financieros', 'Microsoft 365 y dirección SI']],
            'sante' => ['intro' => 'Los proyectos SI sanitarios requieren datos fiables y coordinación entre los actores. OLING acompaña los sistemas de negocio, los ERP y la dirección de programas.', 'specifics' => ['Asesoría de software y coordinación de proyectos SI', 'Continuidad del servicio y calidad de los datos']],
            'transport' => ['intro' => 'Puertos, aeropuertos y operadores de transporte deben coordinar explotación, funciones de soporte y obligaciones normativas. OLING acompaña los sistemas financieros, los ERP, QSE y RGPD.', 'specifics' => ['ERP financiero, RR. HH. y planes directores SI', 'QSE, ciberseguridad, RGPD y continuidad']],
            default => ['intro' => 'Hable con OLING sobre su proyecto SI y sus necesidades de cumplimiento.', 'specifics' => []],
        };
    }

    private function resolveSectorDetailUrl(string $slug, string $locale = SitePageTranslation::LOCALE_FR): ?string
    {
        $publicSlug = match ($slug) {
            'industrie' => 'industrie',
            'transport', 'collectivites', 'collectivités', 'cci', 'eauetassainissement', 'banque', 'mutuelle', 'sante' => 'secteur-public',
            'formationprofessionnelle', 'negoceetdistribution', 'amenagementethabitats' => 'services',
            default => null,
        };

        if ($publicSlug === null) {
            return null;
        }

        $sitePageSlug = self::SECTOR_PAGE_SLUGS[$publicSlug] ?? null;
        $sitePage = $sitePageSlug ? $this->sitePageRepository->findOneBy(['slug' => $sitePageSlug]) : null;

        return $sitePage instanceof SitePage
            ? $this->localizedUrlGenerator?->sitePagePath($sitePage, $locale)
            : ($locale === SitePageTranslation::LOCALE_FR ? '/secteurs/'.$publicSlug : null);
    }
}
