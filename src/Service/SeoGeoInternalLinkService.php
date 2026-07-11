<?php

namespace App\Service;

use App\Repository\SitePageRepository;

class SeoGeoInternalLinkService
{
    private const HEXAGONE_ZONE_SLUGS = [
        'cabinet-conseil-paris',
        'cabinet-conseil-lyon',
        'cabinet-conseil-toulouse',
        'cabinet-conseil-montpellier',
        'cabinet-conseil-nantes',
        'cabinet-conseil-bordeaux',
    ];

    private const OUTRE_MER_ZONE_SLUGS = [
        'cabinet-conseil-guadeloupe',
        'cabinet-conseil-martinique',
        'cabinet-conseil-reunion',
        'cabinet-conseil-guyane',
        'cabinet-conseil-saint-pierre-et-miquelon',
    ];

    private const ALL_ZONE_SLUGS = [
        'cabinet-conseil-paris',
        'cabinet-conseil-lyon',
        'cabinet-conseil-toulouse',
        'cabinet-conseil-montpellier',
        'cabinet-conseil-nantes',
        'cabinet-conseil-bordeaux',
        'cabinet-conseil-guadeloupe',
        'cabinet-conseil-martinique',
        'cabinet-conseil-reunion',
        'cabinet-conseil-guyane',
        'cabinet-conseil-saint-pierre-et-miquelon',
    ];

    private const EXPERTISE_SLUGS = [
        'amoa-si',
        'infrastructure-si-amoa',
        'erp-progiciel',
        'crm',
        'gmao',
        'si-finance',
        'facturation-electronique-amoa',
        'conformite-reglementaire',
        'rgpd',
        'cyber-securite',
        'conseil-qualite',
        'gestion-risques-audit-controle-interne',
        'direction-qualite-deleguee',
        'direction-conformite-externalisee',
        'dsi-externalisee',
        'mapsi-progiciel',
        'public-pme-eti',
    ];

    public function __construct(private SitePageRepository $sitePageRepository)
    {
    }

    public function build(?string $currentPageSlug): array
    {
        if (!$this->isExpertiseSlug($currentPageSlug)) {
            return [];
        }

        $pages = $this->sitePageRepository->findBy(['slug' => self::ALL_ZONE_SLUGS]);
        $pagesBySlug = [];

        foreach ($pages as $page) {
            $slug = $page->getSlug();
            if ($slug === null) {
                continue;
            }

            $pagesBySlug[$slug] = [
                'url' => '/' . $slug,
                'label' => $this->resolvePageLabel($page->getHeroBadge(), $page->getHeroTitle(), $page->getTitle(), $slug),
                'title' => (string) $page->getTitle(),
            ];
        }

        return [
            'hexagone' => $this->orderedLinks(self::HEXAGONE_ZONE_SLUGS, $pagesBySlug),
            'outreMer' => $this->orderedLinks(self::OUTRE_MER_ZONE_SLUGS, $pagesBySlug),
            'hubs' => [
                ['url' => '/metropoles-hexagone', 'label' => 'Metropoles Hexagone'],
                ['url' => '/hexagone-drom-dom-tom', 'label' => 'Hexagone et Outre-mer'],
            ],
        ];
    }

    public function buildExpertiseLinksForZone(?string $currentPageSlug, int $limit = 8): array
    {
        if (!$this->isZoneSlug($currentPageSlug)) {
            return [];
        }

        $pages = $this->sitePageRepository->findBy(['slug' => self::EXPERTISE_SLUGS]);
        $pagesBySlug = [];

        foreach ($pages as $page) {
            $slug = $page->getSlug();
            if ($slug === null) {
                continue;
            }

            $pagesBySlug[$slug] = [
                'url' => '/' . $slug,
                'label' => $this->resolvePageLabel($page->getHeroBadge(), $page->getHeroTitle(), $page->getTitle(), $slug),
                'title' => (string) $page->getTitle(),
            ];
        }

        $links = $this->orderedLinks(self::EXPERTISE_SLUGS, $pagesBySlug);
        if ($limit > 0) {
            $links = array_slice($links, 0, $limit);
        }

        return $links;
    }

    public function isZoneSlug(?string $slug): bool
    {
        return $slug !== null && in_array($slug, self::ALL_ZONE_SLUGS, true);
    }

    public function isExpertiseSlug(?string $slug): bool
    {
        return $slug !== null && in_array($slug, self::EXPERTISE_SLUGS, true);
    }

    private function orderedLinks(array $slugs, array $pagesBySlug): array
    {
        $links = [];
        foreach ($slugs as $slug) {
            if (isset($pagesBySlug[$slug])) {
                $links[] = $pagesBySlug[$slug];
            }
        }

        return $links;
    }

    private function fallbackLabelFromSlug(string $slug): string
    {
        $city = str_replace('cabinet-conseil-', '', $slug);
        $city = str_replace('-', ' ', $city);

        return ucwords($city);
    }

    private function resolvePageLabel(?string $heroBadge, ?string $heroTitle, ?string $title, string $slug): string
    {
        $label = trim((string) ($heroBadge ?? ''));
        if ($label !== '') {
            return $label;
        }

        $label = trim((string) ($heroTitle ?? ''));
        if ($label !== '') {
            return strip_tags($label);
        }

        $label = trim((string) ($title ?? ''));
        if ($label !== '') {
            return strip_tags($label);
        }

        return $this->fallbackLabelFromSlug($slug);
    }
}
