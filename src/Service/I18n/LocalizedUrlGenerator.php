<?php

namespace App\Service\I18n;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Service\LocalizedContentResolver;

final class LocalizedUrlGenerator
{
    public function __construct(
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly LocaleRouteContext $localeRouteContext,
    ) {
    }

    public function sitePagePath(SitePage $page, string $locale): ?string
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $translation = $this->localizedContentResolver->getPublishedTranslation($page, $locale);
        if (!$translation instanceof SitePageTranslation) {
            return null;
        }

        if ($page->getSlug() === 'home') {
            return $locale === SitePageTranslation::LOCALE_FR ? '/' : $this->localeRouteContext->prefixForLocale($locale);
        }

        if ($page->getSlug() === 'ressources') {
            return $this->resourceIndexPath($locale);
        }

        if (str_starts_with((string) $page->getSlug(), 'ressource-')) {
            return $this->resourceArticlePath($page, $locale);
        }

        if ($locale === SitePageTranslation::LOCALE_FR) {
            $fixedPath = $this->fixedFrenchPath($page->getSlug());
            if ($fixedPath !== null) {
                return $fixedPath;
            }
        }

        $slug = trim($translation->getSlug(), '/');
        if ($locale === SitePageTranslation::LOCALE_FR && $slug === 'home') {
            return '/';
        }

        if ($slug === '') {
            return $locale === SitePageTranslation::LOCALE_FR ? '/' : $this->localeRouteContext->prefixForLocale($locale);
        }

        return $this->localeRouteContext->prefixForLocale($locale).'/'.$slug;
    }

    public function resourceIndexPath(string $locale): string
    {
        SitePageTranslation::assertSupportedLocale($locale);

        return match ($locale) {
            SitePageTranslation::LOCALE_EN => '/en/resources',
            SitePageTranslation::LOCALE_ES => '/es/recursos',
            default => '/ressources',
        };
    }

    public function resourceArticlePath(SitePage $page, string $locale): ?string
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $translation = $this->localizedContentResolver->getPublishedTranslation($page, $locale);
        if (!$translation instanceof SitePageTranslation) {
            return null;
        }

        $slug = trim($translation->getSlug(), '/');
        if ($locale === SitePageTranslation::LOCALE_FR) {
            if (!str_starts_with($slug, 'ressource-')) {
                return null;
            }
            $slug = substr($slug, strlen('ressource-'));
        }

        return $slug === '' ? null : $this->resourceIndexPath($locale).'/'.$slug;
    }

    private function fixedFrenchPath(string $sourceSlug): ?string
    {
        return match ($sourceSlug) {
            'team' => '/a-propos/team',
            'projets' => '/projets',
            'secteurs-index' => '/secteurs',
            default => str_starts_with($sourceSlug, 'secteur-') ? '/secteurs/'.$sourceSlug : null,
        };
    }

    /**
     * @return array<string, string>
     */
    public function sitePageAlternates(SitePage $page): array
    {
        $alternates = [];
        foreach (SitePageTranslation::SUPPORTED_LOCALES as $locale) {
            $path = $this->sitePagePath($page, $locale);
            if ($path !== null) {
                $alternates[$locale] = $path;
            }
        }

        if (isset($alternates[SitePageTranslation::LOCALE_FR])) {
            $alternates['x-default'] = $alternates[SitePageTranslation::LOCALE_FR];
        }

        return $alternates;
    }
}
