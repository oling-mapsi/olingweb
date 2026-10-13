<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Service\I18n\LocaleRouteContext;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\LocalizedContentResolver;
use PHPUnit\Framework\TestCase;

final class LocalizedUrlGeneratorTest extends TestCase
{
    public function testInstitutionalAlternatesFallbackWhenLocalizedCmsTranslationIsMissing(): void
    {
        $resolver = $this->createMock(LocalizedContentResolver::class);
        $resolver->method('getPublishedTranslation')->willReturnCallback(
            static fn (SitePage $page, string $locale): ?SitePageTranslation => $locale === SitePageTranslation::LOCALE_FR
                ? (new SitePageTranslation())->setLocale('fr')->setSlug($page->getSlug())->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
                : null
        );

        $generator = new LocalizedUrlGenerator($resolver, new LocaleRouteContext());

        self::assertSame([
            'fr' => '/a-propos',
            'en' => '/en/about',
            'es' => '/es/quienes-somos',
            'x-default' => '/a-propos',
        ], $generator->sitePageAlternates((new SitePage())->setSlug('apropos')));

        self::assertSame('/en/team', $generator->sitePagePath((new SitePage())->setSlug('team'), SitePageTranslation::LOCALE_EN));
    }
}
