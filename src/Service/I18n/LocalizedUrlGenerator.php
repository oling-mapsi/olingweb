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

        $slug = trim($translation->getSlug(), '/');
        if ($locale === SitePageTranslation::LOCALE_FR && $slug === 'home') {
            return '/';
        }

        if ($slug === '') {
            return $locale === SitePageTranslation::LOCALE_FR ? '/' : $this->localeRouteContext->prefixForLocale($locale);
        }

        return $this->localeRouteContext->prefixForLocale($locale).'/'.$slug;
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
