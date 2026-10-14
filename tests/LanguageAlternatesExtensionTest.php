<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Service\I18n\LocaleRouteContext;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\LocalizedContentResolver;
use App\Twig\LanguageAlternatesExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class LanguageAlternatesExtensionTest extends TestCase
{
    public function testAlternatesReflectPublishedTranslationsWithoutStaticMapping(): void
    {
        $page = (new SitePage())->setSlug('demo-page')->setTitle('Demo page');
        $page->addTranslation($this->translation('fr', 'demo-page', SitePageTranslation::STATUS_PUBLISHED));
        $page->addTranslation($this->translation('en', 'demo-page-en', SitePageTranslation::STATUS_DRAFT));
        $page->addTranslation($this->translation('es', 'pagina-demo', SitePageTranslation::STATUS_PUBLISHED));

        $extension = $this->extension([$page]);

        self::assertSame([
            'fr' => '/demo-page',
            'es' => '/es/pagina-demo',
            'x-default' => '/demo-page',
        ], $extension->languageAlternates(Request::create('/demo-page')));

        $page->getTranslation('en')?->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)->setPublishedAt(new \DateTimeImmutable());

        self::assertSame([
            'fr' => '/demo-page',
            'en' => '/en/demo-page-en',
            'es' => '/es/pagina-demo',
            'x-default' => '/demo-page',
        ], $extension->languageAlternates(Request::create('/demo-page')));

        $page->getTranslation('en')?->setTranslationStatus(SitePageTranslation::STATUS_REVIEWED)->setPublishedAt(null);

        self::assertSame([
            'fr' => '/demo-page',
            'es' => '/es/pagina-demo',
            'x-default' => '/demo-page',
        ], $extension->languageAlternates(Request::create('/demo-page')));
    }

    public function testAlternatesResolveFromLocalizedCurrentPath(): void
    {
        $page = (new SitePage())->setSlug('expertises-index')->setTitle('Expertises');
        $page->addTranslation($this->translation('fr', 'expertises-index', SitePageTranslation::STATUS_PUBLISHED));
        $page->addTranslation($this->translation('en', 'areas-of-expertise', SitePageTranslation::STATUS_PUBLISHED));
        $page->addTranslation($this->translation('es', 'areas-de-especializacion', SitePageTranslation::STATUS_PUBLISHED));

        self::assertSame([
            'fr' => '/expertises',
            'en' => '/en/areas-of-expertise',
            'es' => '/es/areas-de-especializacion',
            'x-default' => '/expertises',
        ], $this->extension([$page])->languageAlternates(Request::create('/en/areas-of-expertise')));
    }

    /**
     * @param SitePage[] $pages
     */
    private function extension(array $pages): LanguageAlternatesExtension
    {
        $repository = $this->createMock(SitePageRepository::class);
        $repository->method('findAll')->willReturn($pages);

        $resolver = $this->createMock(LocalizedContentResolver::class);
        $resolver->method('getPublishedTranslation')->willReturnCallback(
            static fn (SitePage $page, string $locale): ?SitePageTranslation => $page->getPublishedTranslation($locale)
        );

        return new LanguageAlternatesExtension(
            $repository,
            new LocalizedUrlGenerator($resolver, new LocaleRouteContext())
        );
    }

    private function translation(string $locale, string $slug, string $status): SitePageTranslation
    {
        $translation = (new SitePageTranslation())
            ->setLocale($locale)
            ->setSlug($slug)
            ->setTitle(strtoupper($locale).' title')
            ->setTranslationStatus($status);

        if ($status === SitePageTranslation::STATUS_PUBLISHED) {
            $translation->setPublishedAt(new \DateTimeImmutable('2026-10-14T12:00:00+00:00'));
        }

        return $translation;
    }
}
