<?php

namespace App\Controller;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\PracticeRepository;
use App\Repository\SitePageRepository;
use App\Repository\ServicesRepository;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\SitePageFaqParser;
use App\Service\LocalizedContentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoResourceController extends AbstractController
{
    private const RESOURCE_ARTICLE_PREFIX = 'ressource-';

    public function __construct(
        private SitePageRepository $sitePageRepository,
        private SitePageFaqParser $sitePageFaqParser,
        private LocalizedContentResolver $localizedContentResolver,
        private LocalizedUrlGenerator $localizedUrlGenerator,
    ) {
    }

    #[Route('/ressources', name: 'seo_resources_index', options: ['sitemap' => true])]
    public function index(Request $request, PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderIndex($request, $practiceRepository, $servicesRepository, SitePageTranslation::LOCALE_FR);
    }

    #[Route('/{_locale}/{resourceIndexSlug}', name: 'localized_resources_index', requirements: ['_locale' => 'en|es', 'resourceIndexSlug' => 'resources|recursos'], methods: ['GET'], priority: 90)]
    public function localizedIndex(Request $request, PracticeRepository $practiceRepository, ServicesRepository $servicesRepository, string $_locale, string $resourceIndexSlug): Response
    {
        SitePageTranslation::assertSupportedLocale($_locale);
        $request->setLocale($_locale);

        $canonicalPath = $this->localizedUrlGenerator->resourceIndexPath($_locale);
        if ($request->getPathInfo() !== $canonicalPath) {
            throw $this->createNotFoundException('Localized resources page not found.');
        }

        return $this->renderIndex($request, $practiceRepository, $servicesRepository, $_locale);
    }

    private function renderIndex(Request $request, PracticeRepository $practiceRepository, ServicesRepository $servicesRepository, string $locale): Response
    {
        $page = $this->sitePageRepository->findResourceIndexPage();
        if ($page === null) {
            throw $this->createNotFoundException('La page ressources est indisponible.');
        }

        $articles = array_values(array_filter(array_map(
            fn (SitePage $resourcePage): ?array => $this->buildResourceCard($resourcePage, $locale),
            $this->sitePageRepository->findResourceArticles()
        )));

        $content = $locale === SitePageTranslation::LOCALE_FR
            ? $this->localizedContentResolver->getFrenchPublicView($page)
            : $this->localizedContentResolver->getPublicView($page, $locale);
        if ($content === null) {
            throw $this->createNotFoundException('Localized resources page not found.');
        }

        return $this->render('seo/resources-index.html.twig', [
            'practices' => array_map(fn ($practice) => $this->localizedContentResolver->getFrenchPracticeView($practice), $practiceRepository->findAll()),
            'services' => array_map(fn ($service) => $this->localizedContentResolver->getFrenchServiceView($service), $servicesRepository->findAll()),
            'pract' => '',
            'page' => $content,
            'pageFaqItems' => $this->sitePageFaqParser->parse($content->getBodyHtml()),
            'articles' => $articles,
            'locale' => $locale,
            'resourcesPath' => $this->localizedUrlGenerator->resourceIndexPath($locale),
            'canonicalPath' => $this->localizedUrlGenerator->resourceIndexPath($locale),
            'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page),
        ]);
    }

    #[Route('/ressources/rss.xml', name: 'seo_resources_rss', methods: ['GET'], options: ['sitemap' => false])]
    public function rss(): Response
    {
        $items = array_values(array_filter(array_map(
            fn (SitePage $resourcePage): ?array => $this->buildResourceFeedItem($resourcePage),
            $this->sitePageRepository->findResourceArticles()
        )));
        usort($items, static fn (array $left, array $right): int => $right['publicationDate'] <=> $left['publicationDate']);
        $items = array_slice($items, 0, 20);

        $response = $this->render('seo/resources-rss.xml.twig', [
            'items' => $items,
        ]);
        $response->headers->set('Content-Type', 'application/rss+xml; charset=UTF-8');
        $response->setPublic();
        $response->setMaxAge(300);
        $response->setSharedMaxAge(300);

        return $response;
    }

    #[Route('/ressources/{slug}', name: 'seo_resource', options: ['sitemap' => false])]
    public function show(
        Request $request,
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
    ): Response {
        $page = $this->sitePageRepository->findResourceArticleByPublicSlug($slug);
        if ($page === null) {
            throw $this->createNotFoundException('La ressource demandee n\'existe pas.');
        }

        return $this->renderArticle($request, $page, $slug, $practiceRepository, $servicesRepository, SitePageTranslation::LOCALE_FR);
    }

    #[Route('/{_locale}/{resourceIndexSlug}/{slug}', name: 'localized_resource', requirements: ['_locale' => 'en|es', 'resourceIndexSlug' => 'resources|recursos', 'slug' => '[a-z0-9][a-z0-9\-]*'], methods: ['GET'], priority: 90)]
    public function localizedShow(
        Request $request,
        string $_locale,
        string $resourceIndexSlug,
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
    ): Response {
        SitePageTranslation::assertSupportedLocale($_locale);
        $request->setLocale($_locale);

        $content = $this->localizedContentResolver->getPublishedPublicViewByLocalizedSlug($_locale, $slug);
        $page = $content?->getSourcePage();
        if (!$page instanceof SitePage || !str_starts_with((string) $page->getSlug(), self::RESOURCE_ARTICLE_PREFIX)) {
            throw $this->createNotFoundException('Localized resource not found.');
        }

        $canonicalPath = $this->localizedUrlGenerator->resourceArticlePath($page, $_locale);
        if ($request->getPathInfo() !== $canonicalPath) {
            throw $this->createNotFoundException('Localized resource not found.');
        }

        return $this->renderArticle($request, $page, $slug, $practiceRepository, $servicesRepository, $_locale);
    }

    private function renderArticle(
        Request $request,
        SitePage $page,
        string $publicSlug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        string $locale,
    ): Response {
        $related = array_values(array_filter(array_map(
            fn (SitePage $resourcePage): ?array => $this->buildResourceCard($resourcePage, $locale),
            $this->sitePageRepository->findRelatedResourceArticles((string) $page->getSlug(), 4)
        )));

        $content = $locale === SitePageTranslation::LOCALE_FR
            ? $this->localizedContentResolver->getFrenchPublicView($page)
            : $this->localizedContentResolver->getPublicView($page, $locale);
        if ($content === null) {
            throw $this->createNotFoundException('Localized resource not found.');
        }

        return $this->render('seo/resource-article.html.twig', [
            'practices' => array_map(fn ($practice) => $this->localizedContentResolver->getFrenchPracticeView($practice), $practiceRepository->findAll()),
            'services' => array_map(fn ($service) => $this->localizedContentResolver->getFrenchServiceView($service), $servicesRepository->findAll()),
            'pract' => '',
            'page' => $content,
            'pageFaqItems' => $this->sitePageFaqParser->parse($content->getBodyHtml()),
            'publicSlug' => $publicSlug,
            'related' => $related,
            'locale' => $locale,
            'resourcesPath' => $this->localizedUrlGenerator->resourceIndexPath($locale),
            'canonicalPath' => $this->localizedUrlGenerator->resourceArticlePath($page, $locale),
            'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page),
        ]);
    }

    /**
     * @return array{slug: string, title: string, h1: string, intro: string}|null
     */
    private function buildResourceCard(SitePage $page, string $locale = SitePageTranslation::LOCALE_FR): ?array
    {
        $content = $locale === SitePageTranslation::LOCALE_FR
            ? $this->localizedContentResolver->getFrenchPublicView($page)
            : $this->localizedContentResolver->getPublicView($page, $locale);
        if ($content === null) {
            return null;
        }

        $storedSlug = $content->getSlug();
        if ($locale === SitePageTranslation::LOCALE_FR && !str_starts_with($storedSlug, self::RESOURCE_ARTICLE_PREFIX)) {
            return null;
        }

        $publicSlug = $locale === SitePageTranslation::LOCALE_FR
            ? substr($storedSlug, strlen(self::RESOURCE_ARTICLE_PREFIX))
            : trim($storedSlug, '/');
        if ($publicSlug === false || $publicSlug === '') {
            return null;
        }

        return [
            'slug' => $publicSlug,
            'href' => $this->localizedUrlGenerator->resourceArticlePath($page, $locale),
            'title' => $content->getTitle(),
            'h1' => (string) ($content->getHeroTitle() ?: $content->getTitle()),
            'intro' => (string) ($content->getHeroIntro() ?: ''),
        ];
    }

    /**
     * @return array{slug: string, title: string, summary: string, publicationDate: \DateTimeImmutable}|null
     */
    private function buildResourceFeedItem(SitePage $page): ?array
    {
        $card = $this->buildResourceCard($page);
        $publicationDate = $page->getPublicationDate() ?? $page->getPublishedAt();
        if ($card === null || $publicationDate === null) {
            return null;
        }

        $content = $this->localizedContentResolver->getFrenchPublicView($page);
        $summary = $content->getHeroIntro() ?: $content->getMetaDescription() ?: $card['h1'];
        $summary = html_entity_decode(strip_tags($summary), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $summary = trim((string) preg_replace('/\s+/u', ' ', $summary));

        return [
            'slug' => $card['slug'],
            'title' => $card['h1'],
            'summary' => $summary,
            'publicationDate' => $publicationDate,
        ];
    }
}
