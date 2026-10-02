<?php

namespace App\Service\I18n;

use App\Entity\SitePageTranslation;

final class LocaleRouteContext
{
    public const SOURCE_LOCALE = SitePageTranslation::LOCALE_FR;
    public const PREFIXED_LOCALES = [SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES];
    public const RESERVED_PREFIXES = [SitePageTranslation::LOCALE_FR, SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES];

    public function localeFromPath(string $path): string
    {
        $trimmed = trim($path, '/');
        $firstSegment = $trimmed === '' ? '' : explode('/', $trimmed, 2)[0];

        return in_array($firstSegment, self::PREFIXED_LOCALES, true) ? $firstSegment : self::SOURCE_LOCALE;
    }

    public function prefixForLocale(string $locale): string
    {
        SitePageTranslation::assertSupportedLocale($locale);

        return $locale === self::SOURCE_LOCALE ? '' : '/'.$locale;
    }

    public function isReservedRootSlug(string $slug): bool
    {
        return in_array($slug, self::RESERVED_PREFIXES, true);
    }
}
