<?php

namespace App\Service;

use App\Entity\SitePage;
use App\Entity\Metier;
use App\Repository\MetierRepository;
use App\Repository\SitePageRepository;

class PublicSitePageResolver
{
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

    private const SECTOR_PAGE_SLUGS = [
        'industrie' => 'secteur-industrie',
        'services' => 'secteur-services',
        'secteur-public' => 'secteur-secteur-public',
    ];

    public function __construct(
        private readonly PublicSiteConfig $publicSiteConfig,
        private readonly SitePageRepository $sitePageRepository,
        private readonly MetierRepository $metierRepository,
    ) {
    }

    public function getHomePage(): array
    {
        $defaults = $this->publicSiteConfig->getHome();
        $sitePage = $this->sitePageRepository->findOneBy(['slug' => self::HOME_PAGE_SLUG]);

        if ($sitePage === null) {
            return $defaults;
        }

        $payload = $this->decodeStructuredPayload($sitePage->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);

        $merged['seoTitle'] = $sitePage->getTitle() ?: ($merged['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $sitePage->getMetaDescription() ?: ($merged['metaDescription'] ?? $defaults['metaDescription']);

        if ($sitePage->getHeroBadge() !== null) {
            $merged['hero']['eyebrow'] = trim(strip_tags($sitePage->getHeroBadge()));
        } else {
            $merged['hero']['eyebrow'] = $merged['hero']['eyebrow'] ?? $defaults['hero']['eyebrow'];
        }

        if ($sitePage->getHeroIntro() !== null) {
            $merged['hero']['intro'] = trim(strip_tags($sitePage->getHeroIntro()));
        } else {
            $merged['hero']['intro'] = $merged['hero']['intro'] ?? $defaults['hero']['intro'];
        }

        $titleOverride = $sitePage->getHeroTitle();
        if ($titleOverride !== null) {
            $lines = array_values(array_filter(array_map(
                static fn (string $line): string => trim(strip_tags($line)),
                explode('|', $titleOverride)
            )));
            $merged['hero']['titleLines'] = $lines;
        }

        if ($sitePage->getHeroImage()) {
            $merged['hero']['portraitImage'] = $sitePage->getHeroImage();
        }

        if (str_contains(strtolower((string) ($merged['hero']['badge'] ?? '')), 'iso 27001')) {
            $merged['hero']['badge'] = '';
        }

        $metierSlug = $merged['hero']['metierSlug'] ?? null;
        if (is_string($metierSlug) && trim($metierSlug) !== '') {
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

    public function getEditorialPage(string $slug): array
    {
        $defaults = $this->publicSiteConfig->getEditorialPages()[$slug] ?? [];
        $sitePage = $this->sitePageRepository->findOneBy(['slug' => $slug]);

        if ($sitePage === null) {
            return $defaults;
        }

        $payload = $this->decodeStructuredPayload($sitePage->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);

        $merged['seoTitle'] = $sitePage->getTitle() ?: ($merged['seoTitle'] ?? '');
        $merged['metaDescription'] = $sitePage->getMetaDescription() ?: ($merged['metaDescription'] ?? '');
        $merged['eyebrow'] = $sitePage->getHeroBadge() ?: ($merged['eyebrow'] ?? '');
        $merged['title'] = $sitePage->getHeroTitle() ?: ($merged['title'] ?? '');
        $merged['intro'] = $this->plainText($sitePage->getHeroIntro()) ?: ($merged['intro'] ?? '');
        $merged['heroImage'] = $sitePage->getHeroImage() ?: ($merged['heroImage'] ?? null);

        if ($sitePage->getHeroSideHtml()) {
            $merged['legacySideHtml'] = $sitePage->getHeroSideHtml();
        }

        if ($sitePage->getBodyHtml() && $payload === []) {
            $merged['legacyBodyHtml'] = $sitePage->getBodyHtml();
        }

        return $merged;
    }

    public function getExpertisesIndex(): array
    {
        return $this->mergeIndexPage(
            $this->sitePageRepository->findOneBy(['slug' => self::EXPERTISE_INDEX_SLUG]),
            [
                'seoTitle' => 'Expertises | OLING',
                'metaDescription' => 'Découvrez les expertises OLING pour les PME, PMI et ETI : transformation SI, AMOA ERP, organisation, data, IA, cybersécurité et conformité.',
                'eyebrow' => 'Expertises OLING',
                'title' => 'AMO ERP, risques, conformité, RGPD, IA : les expertises qui tiennent les transformations',
                'intro' => 'OLING structure son accompagnement autour des sujets qui exposent directement la direction : arbitrages SI, projets ERP, conformité, gouvernance des données, résilience et usages IA.',
                'highlights' => ['AMO ERP et applications métiers', 'Risques, cyber et résilience', 'RGPD, gouvernance et IA'],
                'primaryCta' => ['route' => 'contact', 'label' => 'Parler à un consultant'],
                'secondaryCta' => ['route' => 'projets', 'label' => 'Voir des cas clients'],
                'section' => [
                    'kicker' => 'Vos besoins',
                    'title' => 'Les expertises structurent les sujets à arbitrer avant de choisir le bon dispositif',
                    'text' => 'Cette couche éditoriale aide à qualifier le problème à traiter. Une fois le sujet clarifié, les pages services détaillent le niveau d’intervention mobilisable, et les pages secteurs montrent comment ces expertises s’appliquent selon le contexte métier.',
                ],
                'cta' => [
                    'title' => 'Besoin d\'un regard indépendant avant de relancer ou sécuriser un sujet critique',
                    'text' => 'OLING intervient quand un projet ERP, une trajectoire SI, un risque de conformité ou un sujet IA doivent redevenir pilotables rapidement.',
                    'primaryCta' => ['route' => 'contact', 'label' => 'Demander un échange'],
                ],
            ]
        );
    }

    public function getExpertisePages(): array
    {
        $defaults = $this->publicSiteConfig->getExpertisePages();

        foreach ($defaults as $slug => $page) {
            $defaults[$slug] = $this->mergeStructuredPage(
                $this->sitePageRepository->findOneBy(['slug' => self::EXPERTISE_PAGE_SLUGS[$slug] ?? null]),
                $page
            );
        }

        return $defaults;
    }

    public function getSectorsIndex(): array
    {
        return $this->mergeIndexPage(
            $this->sitePageRepository->findOneBy(['slug' => self::SECTOR_INDEX_SLUG]),
            [
                'seoTitle' => 'Secteurs | OLING',
                'metaDescription' => 'Découvrez l’ensemble des secteurs servis par OLING et les points de vigilance connus selon chaque environnement métier.',
                'eyebrow' => 'Secteurs servis',
                'title' => 'Les secteurs métiers accompagnés par OLING',
                'intro' => 'OLING intervient dans des environnements très différents, avec une même logique : comprendre les contraintes opérationnelles, clarifier les priorités et sécuriser les transformations utiles.',
                'highlights' => ['11 secteurs servis', 'Références terrain publiées', 'Contraintes opérationnelles et réglementaires'],
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
            ]
        );
    }

    public function getSectorPages(): array
    {
        $defaults = $this->publicSiteConfig->getSectorPages();

        foreach ($defaults as $slug => $page) {
            $defaults[$slug] = $this->mergeStructuredPage(
                $this->sitePageRepository->findOneBy(['slug' => self::SECTOR_PAGE_SLUGS[$slug] ?? null]),
                $page
            );
        }

        return $defaults;
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
    public function getSectorCatalogEntries(): array
    {
        $entries = [];

        foreach ($this->getPublishedMetiers() as $metier) {
            $slug = (string) $metier->getSlug();
            $designation = trim((string) $metier->getDesignation());
            $summary = $this->getSectorSummary($slug, $designation);
            $projectCount = $metier->getProjets()->count();

            $entries[] = [
                'slug' => $slug,
                'designation' => $designation,
                'intro' => $summary['intro'],
                'specifics' => $summary['specifics'],
                'projectCount' => $projectCount,
                'heroImage' => $metier->getImageHero() ?: $metier->getImage(),
                'detailUrl' => $this->resolveSectorDetailUrl($slug),
            ];
        }

        return $entries;
    }

    private function mergeIndexPage(?SitePage $sitePage, array $defaults): array
    {
        if ($sitePage === null) {
            return $defaults;
        }

        $payload = $this->decodeStructuredPayload($sitePage->getBodyHtml());
        $merged = $this->mergeRecursive($defaults, $payload);
        $merged['seoTitle'] = $sitePage->getTitle() ?: ($merged['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $sitePage->getMetaDescription() ?: ($merged['metaDescription'] ?? $defaults['metaDescription']);
        $merged['eyebrow'] = $sitePage->getHeroBadge() ?: ($merged['eyebrow'] ?? $defaults['eyebrow']);
        $merged['title'] = $sitePage->getHeroTitle() ?: ($merged['title'] ?? $defaults['title']);
        $merged['intro'] = $this->plainText($sitePage->getHeroIntro()) ?: ($merged['intro'] ?? $defaults['intro']);

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

        $payload = $this->decodeStructuredPayload($sitePage->getBodyHtml());

        $merged = $defaults;
        $merged['title'] = $sitePage->getHeroTitle() ?: ($payload['title'] ?? $defaults['title']);
        $merged['seoTitle'] = $sitePage->getTitle() ?: ($payload['seoTitle'] ?? $defaults['seoTitle']);
        $merged['metaDescription'] = $sitePage->getMetaDescription() ?: ($payload['metaDescription'] ?? $defaults['metaDescription']);
        $merged['eyebrow'] = $sitePage->getHeroBadge() ?: ($payload['eyebrow'] ?? $defaults['eyebrow']);
        $merged['intro'] = $this->plainText($sitePage->getHeroIntro()) ?: ($payload['intro'] ?? $defaults['intro']);
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
    private function getSectorSummary(string $slug, string $designation): array
    {
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

    private function resolveSectorDetailUrl(string $slug): ?string
    {
        return match ($slug) {
            'industrie' => '/secteurs/industrie',
            'transport', 'collectivites', 'cci', 'eauetassainissement', 'banque', 'mutuelle', 'sante' => '/secteurs/secteur-public',
            'formationprofessionnelle', 'negoceetdistribution', 'amenagementethabitats' => '/secteurs/services',
            default => null,
        };
    }
}
