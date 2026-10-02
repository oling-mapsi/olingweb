<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\LocalizedSlugHistoryRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\I18n\LocaleRouteContext;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\LocalizedContentResolver;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Yaml\Yaml;

final class I18nRoutingFoundationTest extends TestCase
{
    public function testDefaultLocaleIsFrench(): void
    {
        $config = Yaml::parseFile(dirname(__DIR__).'/config/packages/translation.yaml');

        self::assertSame('fr', $config['framework']['default_locale']);
        self::assertSame(['fr'], $config['framework']['translator']['fallbacks']);
    }

    public function testLocaleIsDerivedFromUrlOnly(): void
    {
        $context = new LocaleRouteContext();

        self::assertSame('fr', $context->localeFromPath('/erp-progiciel'));
        self::assertSame('fr', $context->localeFromPath('/'));
        self::assertSame('en', $context->localeFromPath('/en/erp-software'));
        self::assertSame('es', $context->localeFromPath('/es/software-erp'));
        self::assertTrue($context->isReservedRootSlug('en'));
        self::assertTrue($context->isReservedRootSlug('es'));
    }

    public function testLocalizedSitePageUrlsUsePublishedLocalizedSlugs(): void
    {
        $page = $this->pageWithTranslations([
            $this->translation('fr', 'erp-progiciel', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('es', 'software-erp', SitePageTranslation::STATUS_PUBLISHED),
        ]);
        $generator = $this->urlGenerator();

        self::assertSame('/erp-progiciel', $generator->sitePagePath($page, 'fr'));
        self::assertSame('/en/erp-software', $generator->sitePagePath($page, 'en'));
        self::assertSame('/es/software-erp', $generator->sitePagePath($page, 'es'));
    }

    public function testMissingOrDraftTranslationDoesNotFallbackToFrench(): void
    {
        $page = $this->pageWithTranslations([
            $this->translation('fr', 'erp-progiciel', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('en', 'erp-software', SitePageTranslation::STATUS_DRAFT),
        ]);
        $generator = $this->urlGenerator();

        self::assertSame('/erp-progiciel', $generator->sitePagePath($page, 'fr'));
        self::assertNull($generator->sitePagePath($page, 'en'));
        self::assertNull($generator->sitePagePath($page, 'es'));
    }

    public function testAlternatesExposeOnlyPublishedIndexableLocales(): void
    {
        $page = $this->pageWithTranslations([
            $this->translation('fr', 'erp-progiciel', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('es', 'software-erp', SitePageTranslation::STATUS_TO_REVIEW),
        ]);

        self::assertSame([
            'fr' => '/erp-progiciel',
            'en' => '/en/erp-software',
            'x-default' => '/erp-progiciel',
        ], $this->urlGenerator()->sitePageAlternates($page));
    }

    public function testHomeFrenchCanonicalPathUsesRootWithLocalizedAlternates(): void
    {
        $page = (new SitePage())->setSlug('home')->setTitle('Home');
        foreach ([
            $this->translation('fr', 'home', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('en', 'home', SitePageTranslation::STATUS_PUBLISHED),
            $this->translation('es', 'inicio', SitePageTranslation::STATUS_PUBLISHED),
        ] as $translation) {
            $page->addTranslation($translation);
        }

        self::assertSame([
            'fr' => '/',
            'en' => '/en/home',
            'es' => '/es/inicio',
            'x-default' => '/',
        ], $this->urlGenerator()->sitePageAlternates($page));
    }

    public function testResolverReturnsOnlyPublishedLocalizedSlug(): void
    {
        $published = $this->translation('en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED);
        $page = $this->pageWithTranslations([$published]);
        $repository = $this->createMock(SitePageTranslationRepository::class);
        $repository->method('findOnePublishedByLocaleAndSlug')->willReturnCallback(
            static fn (string $locale, string $slug): ?SitePageTranslation => $locale === 'en' && $slug === 'erp-software' ? $published : null
        );

        $resolver = new LocalizedContentResolver(
            $repository,
            $this->createMock(LocalizedSlugHistoryRepository::class),
            $this->createMock(Connection::class)
        );

        self::assertSame($page, $resolver->getPublishedPublicViewByLocalizedSlug('en', 'erp-software')?->getSourcePage());
        self::assertNull($resolver->getPublishedPublicViewByLocalizedSlug('en', 'erp-progiciel'));
    }

    /**
     * @param SitePageTranslation[] $translations
     */
    private function pageWithTranslations(array $translations): SitePage
    {
        $page = (new SitePage())->setSlug('erp-progiciel')->setTitle('ERP progiciel');
        foreach ($translations as $translation) {
            $page->addTranslation($translation);
        }

        return $page;
    }

    private function translation(string $locale, string $slug, string $status): SitePageTranslation
    {
        $translation = (new SitePageTranslation())
            ->setLocale($locale)
            ->setSlug($slug)
            ->setTitle(strtoupper($locale).' title')
            ->setTranslationStatus($status);

        if ($status === SitePageTranslation::STATUS_PUBLISHED) {
            $translation->setPublishedAt(new \DateTimeImmutable('2026-10-02 12:00:00'));
        }

        return $translation;
    }

    private function urlGenerator(): LocalizedUrlGenerator
    {
        return new LocalizedUrlGenerator(
            new LocalizedContentResolver(
                $this->createMock(SitePageTranslationRepository::class),
                $this->createMock(LocalizedSlugHistoryRepository::class),
                $this->createMock(Connection::class)
            ),
            new LocaleRouteContext()
        );
    }
}
