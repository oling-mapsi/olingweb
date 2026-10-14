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
        $merged['heroImage'] = $sitePage->getHeroImage() ?: ($merged['heroImage'] ?? null);

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
                    'title' => 'Les expertises structurent les sujets à arbitrer avant de choisir le bon dispositif',
                    'text' => 'Cette couche éditoriale aide à qualifier le problème à traiter. Une fois le sujet clarifié, les pages services détaillent le niveau d’intervention mobilisable, et les pages secteurs montrent comment ces expertises s’appliquent selon le contexte métier.',
                ],
                'cta' => [
                    'title' => 'Besoin d\'un regard indépendant avant de relancer ou sécuriser un sujet critique ?',
                    'text' => 'OLING intervient quand un projet ERP, une trajectoire SI, un risque de conformité ou un sujet IA doivent redevenir pilotables rapidement.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Demander un échange'],
                ],
            ],
            SitePageTranslation::LOCALE_EN => [
                'primaryCta' => ['route' => 'contact', 'label' => 'Talk to a consultant'],
                'secondaryCta' => ['route' => 'projets', 'label' => 'View client cases'],
                'section' => [
                    'kicker' => 'Your needs',
                    'title' => 'Expertise areas structure the issues to decide before choosing the right setup',
                    'text' => 'This editorial layer helps qualify the problem first. Once the issue is clear, service pages detail the level of support available and sector pages show how these areas apply in each business context.',
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
                    'title' => 'Las especializaciones estructuran los temas que deben decidirse antes de elegir el dispositivo adecuado',
                    'text' => 'Esta capa editorial ayuda primero a calificar el problema. Una vez aclarado el tema, las páginas de servicios detallan el nivel de intervención disponible y las páginas sectoriales muestran cómo se aplican estas especializaciones según el contexto de negocio.',
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
                    'title' => 'Une lecture simple des secteurs réellement couverts',
                    'text' => 'Cette page reprend les secteurs présents dans l’admin et les met en regard des contextes déjà traités par OLING. Quand des signaux métier ressortent des références publiées, ils sont indiqués directement.',
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
                    'text' => 'This page lists the sectors present in the admin and connects them with contexts already handled by OLING. When business signals stand out in published references, they are shown directly.',
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
                    'text' => 'Esta página retoma los sectores presentes en el admin y los pone en relación con contextos ya tratados por OLING. Cuando aparecen señales de negocio en referencias publicadas, se indican directamente.',
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
                'eyebrow' => 'Secteurs servis',
                'title' => 'Les secteurs métiers accompagnés par OLING',
                'intro' => 'OLING intervient dans des environnements très différents, avec une même logique : comprendre les contraintes opérationnelles, clarifier les priorités et sécuriser les transformations utiles.',
                'highlights' => ['11 secteurs servis', 'Références terrain publiées', 'Contraintes opérationnelles et réglementaires'],
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

        foreach ($this->getPublishedMetiers() as $metier) {
            $slug = (string) $metier->getSlug();
            $designation = $this->localizedSectorDesignation($slug, trim((string) $metier->getDesignation()), $locale);
            $summary = $this->getSectorSummary($slug, $designation, $locale);
            $projectCount = $metier->getProjets()->count();

            $entries[] = [
                'slug' => $slug,
                'designation' => $designation,
                'intro' => $summary['intro'],
                'specifics' => $summary['specifics'],
                'projectCount' => $projectCount,
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
        $payload['heroImage'] = $sitePage->getHeroImage() ?: ($payload['heroImage'] ?? null);

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
                'intro' => 'Interventions sur des contextes d’aménagement, d’habitat et de gestion patrimoniale avec enjeux de gouvernance SI et d’organisation.',
                'specifics' => [
                    'Bailleurs sociaux et structures d’aménagement',
                    'Gouvernance SI, direction des systèmes d’information et structuration du pilotage',
                ],
            ],
            'banque' => [
                'intro' => 'Contextes bancaires exposés à des exigences fortes de continuité, d’infrastructure critique et de pilotage de trajectoires techniques.',
                'specifics' => [
                    'Datacenter, virtualisation et schémas de transformation techniques',
                    'Exigence de fiabilité, de sécurité et de planification pluriannuelle',
                ],
            ],
            'cci' => [
                'intro' => 'Accompagnements auprès de chambres consulaires sur les sujets de digitalisation, d’ERP, de stockage et de conformité.',
                'specifics' => [
                    'Refonte de processus et d’outils pour centres de formation et guichets',
                    'Schéma directeur, RGPD et modernisation du système d’information',
                ],
            ],
            'collectivites' => [
                'intro' => 'Travail avec des collectivités et structures parapubliques sur la transformation SI, les télécoms, la collaboration et la gouvernance.',
                'specifics' => [
                    'Schémas directeurs, AMOA SI et catalogues de services',
                    'Environnements multi-acteurs avec enjeux de mutualisation et de traçabilité',
                ],
            ],
            'eauetassainissement' => [
                'intro' => 'Interventions sur des organisations eau et assainissement avec enjeux de convergence SI, continuité et exploitation.',
                'specifics' => [
                    'Convergence de systèmes hérités ou dispersés',
                    'Pilotage d’activités critiques de réseau et de service public',
                ],
            ],
            'formationprofessionnelle' => [
                'intro' => 'Expérience forte dans la formation professionnelle, à l’interface entre qualité, conformité, outils de gestion et organisation.',
                'specifics' => [
                    'Qualiopi, QSE, RGPD et direction qualité externalisée',
                    'Sites web, plateaux techniques, outils de gestion et gouvernance documentaire',
                ],
            ],
            'industrie' => [
                'intro' => 'Interventions industrielles centrées sur l’ERP, le CRM, la conformité et la structuration de la fonction SI.',
                'specifics' => [
                    'ERP, outils métiers et coordination avec la production',
                    'RGPD, DSI externalisée et contextes multi-sites ou actifs critiques',
                ],
            ],
            'mutuelle' => [
                'intro' => 'Accompagnements dans la mutuelle et l’assurance sur le pilotage, la donnée, la continuité et le contrôle de gestion.',
                'specifics' => [
                    'PCA, BIA et dispositifs de résilience',
                    'Données santé, prévoyance et suivi de gestion',
                ],
            ],
            'negoceetdistribution' => [
                'intro' => 'Missions auprès d’acteurs du négoce et de la distribution sur l’ERP, Office 365, la normalisation comptable et la fonction SI.',
                'specifics' => [
                    'Gestion commerciale, comptabilité, achats et collaboration',
                    'Déploiement ou reprise d’ERP dans des environnements opérationnels',
                ],
            ],
            'sante' => [
                'intro' => 'Présence sur des sujets santé mêlant SI métier, ERP et coordination d’acteurs à fortes contraintes de fiabilité.',
                'specifics' => [
                    'Vaccination, répartition pharmaceutique et processus sensibles',
                    'Pilotage SI avec exigences de continuité et de qualité de données',
                ],
            ],
            'transport' => [
                'intro' => 'Références nombreuses dans le transport, les grands ports et les plateformes aéroportuaires, sur des sujets SI, qualité, conformité et continuité.',
                'specifics' => [
                    'ERP finance, paie, GRH, télécoms, GED et schémas directeurs',
                    'ISO, RGPD, continuité d’activité et gouvernance multi-sites',
                ],
            ],
            default => [
                'intro' => sprintf('OLING intervient dans le secteur %s avec une approche de cadrage, de pilotage et de sécurisation des transformations.', mb_strtolower($designation)),
                'specifics' => [
                    'Organisation, outils et gouvernance',
                    'Contraintes métier, SI et conformité selon le contexte',
                ],
            ],
        };
    }

    private function getEnglishSectorSummary(string $slug, string $designation): array
    {
        return match ($slug) {
            'industrie' => ['intro' => 'Industrial engagements focused on ERP, CRM, compliance and structuring the IT function.', 'specifics' => ['ERP, business tools and production coordination', 'GDPR, outsourced CIO support and multi-site or critical-asset contexts']],
            'collectivites' => ['intro' => 'Work with local authorities and public-sector bodies on IT transformation, telecoms, collaboration and governance.', 'specifics' => ['IT roadmaps, IT project advisory and service catalogues', 'Multi-stakeholder settings with pooling and traceability requirements']],
            'transport' => ['intro' => 'Multiple references in transport, major ports and airport platforms across IT, quality, compliance and continuity topics.', 'specifics' => ['Finance ERP, payroll, HRIS, telecoms, EDM and IT roadmaps', 'ISO, GDPR, business continuity and multi-site governance']],
            default => ['intro' => sprintf('OLING supports the %s sector with a scoping, steering and transformation-risk approach.', mb_strtolower($designation)), 'specifics' => ['Organisation, tools and governance', 'Business, IT and compliance constraints depending on context']],
        };
    }

    private function getSpanishSectorSummary(string $slug, string $designation): array
    {
        return match ($slug) {
            'industrie' => ['intro' => 'Intervenciones industriales centradas en ERP, CRM, cumplimiento y estructuración de la función SI.', 'specifics' => ['ERP, herramientas de negocio y coordinación con producción', 'RGPD, DSI externalizada y contextos multisede o activos críticos']],
            'collectivites' => ['intro' => 'Trabajo con administraciones locales y entidades públicas en transformación SI, telecomunicaciones, colaboración y gobernanza.', 'specifics' => ['Planes directores SI, AMOA SI y catálogos de servicios', 'Entornos multiactor con mutualización y trazabilidad']],
            'transport' => ['intro' => 'Referencias en transporte, grandes puertos y plataformas aeroportuarias sobre SI, calidad, cumplimiento y continuidad.', 'specifics' => ['ERP financiero, nómina, RR. HH., telecomunicaciones, GED y planes directores', 'ISO, RGPD, continuidad de actividad y gobernanza multisede']],
            default => ['intro' => sprintf('OLING interviene en el sector %s con un enfoque de encuadre, pilotaje y securización de transformaciones.', mb_strtolower($designation)), 'specifics' => ['Organización, herramientas y gobernanza', 'Restricciones de negocio, SI y cumplimiento según el contexto']],
        };
    }

    private function resolveSectorDetailUrl(string $slug, string $locale = SitePageTranslation::LOCALE_FR): ?string
    {
        $publicSlug = match ($slug) {
            'industrie' => 'industrie',
            'transport', 'collectivites', 'cci', 'eauetassainissement', 'banque', 'mutuelle', 'sante' => 'secteur-public',
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
