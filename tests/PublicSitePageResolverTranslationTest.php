<?php

namespace App\Tests;

use App\Dto\SitePagePublicView;
use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\MetierRepository;
use App\Repository\SiteGlobalContentRepository;
use App\Repository\SiteGlobalContentTranslationRepository;
use App\Repository\SitePageRepository;
use App\Service\LocalizedContentResolver;
use App\Service\PublicSitePageResolver;
use PHPUnit\Framework\TestCase;

class PublicSitePageResolverTranslationTest extends TestCase
{
    public function testEditorialPageReadsFrenchTranslationInsteadOfLegacyFields(): void
    {
        $page = (new SitePage())
            ->setSlug('contact')
            ->setTitle('LEGACY TITLE SHOULD NOT APPEAR')
            ->setMetaDescription('LEGACY META SHOULD NOT APPEAR')
            ->setHeroTitle('LEGACY HERO SHOULD NOT APPEAR')
            ->setHeroIntro('LEGACY INTRO SHOULD NOT APPEAR')
            ->setBodyHtml('{"legacy":"body"}');

        $translation = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('contact')
            ->setTitle('TRANSLATED FR TITLE')
            ->setSeoTitle('TRANSLATED FR SEO TITLE')
            ->setSeoDescription('TRANSLATED FR META')
            ->setHeroTitle('TRANSLATED FR HERO')
            ->setHeroIntro('TRANSLATED FR INTRO')
            ->setBodyHtml('{"formSection":{"title":"TRANSLATED FR BODY MARKER"}}');

        $sitePageRepository = $this->createMock(SitePageRepository::class);
        $sitePageRepository->method('findOneBy')->willReturn($page);

        $localizedContentResolver = $this->createMock(LocalizedContentResolver::class);
        $localizedContentResolver
            ->method('getPublicView')
            ->with($page, SitePageTranslation::LOCALE_FR)
            ->willReturn(new SitePagePublicView($page, $translation));

        $resolver = new PublicSitePageResolver(
            $sitePageRepository,
            $this->createMock(MetierRepository::class),
            $localizedContentResolver,
            $this->createMock(SiteGlobalContentRepository::class),
            $this->createMock(SiteGlobalContentTranslationRepository::class)
        );

        $resolved = $resolver->getEditorialPage('contact');

        self::assertSame('TRANSLATED FR SEO TITLE', $resolved['seoTitle']);
        self::assertSame('TRANSLATED FR META', $resolved['metaDescription']);
        self::assertSame('TRANSLATED FR HERO', $resolved['title']);
        self::assertSame('TRANSLATED FR INTRO', $resolved['intro']);
        self::assertSame('TRANSLATED FR BODY MARKER', $resolved['formSection']['title']);
        self::assertStringNotContainsString('LEGACY', json_encode($resolved, JSON_THROW_ON_ERROR));
    }

    public function testInstitutionalSeoFallbackAppliesWhenLocalizedCmsPageIsMissing(): void
    {
        $page = (new SitePage())->setSlug('apropos');

        $sitePageRepository = $this->createMock(SitePageRepository::class);
        $sitePageRepository->method('findOneBy')->willReturn($page);

        $localizedContentResolver = $this->createMock(LocalizedContentResolver::class);
        $localizedContentResolver
            ->method('getPublicView')
            ->with($page, SitePageTranslation::LOCALE_EN)
            ->willReturn(null);

        $resolver = new PublicSitePageResolver(
            $sitePageRepository,
            $this->createMock(MetierRepository::class),
            $localizedContentResolver,
            $this->createMock(SiteGlobalContentRepository::class),
            $this->createMock(SiteGlobalContentTranslationRepository::class)
        );

        $resolved = $resolver->getEditorialPage('apropos', SitePageTranslation::LOCALE_EN);

        self::assertSame('About OLING | IT Project Advisory Consultancy', $resolved['seoTitle']);
        self::assertSame('OLING provides IT project advisory, enterprise software and compliance consulting, covering governance, ERP and organisational support.', $resolved['metaDescription']);
        self::assertSame('IT project advisory, enterprise software and compliance', $resolved['title']);
    }
}
