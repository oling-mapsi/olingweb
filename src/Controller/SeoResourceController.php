<?php

namespace App\Controller;

use App\Entity\SitePage;
use App\Repository\PracticeRepository;
use App\Repository\SitePageRepository;
use App\Repository\ServicesRepository;
use App\Service\SitePageFaqParser;
use App\Service\LocalizedContentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoResourceController extends AbstractController
{
    private const RESOURCE_ARTICLE_PREFIX = 'ressource-';

    public function __construct(
        private SitePageRepository $sitePageRepository,
        private SitePageFaqParser $sitePageFaqParser,
        private LocalizedContentResolver $localizedContentResolver
    ) {
    }

    #[Route('/ressources', name: 'seo_resources_index', options: ['sitemap' => true])]
    public function index(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        $page = $this->sitePageRepository->findResourceIndexPage();
        if ($page === null) {
            throw $this->createNotFoundException('La page ressources est indisponible.');
        }

        $articles = array_values(array_filter(array_map(
            fn (\App\Entity\SitePage $resourcePage): ?array => $this->buildResourceCard($resourcePage),
            $this->sitePageRepository->findResourceArticles()
        )));

        $content = $this->localizedContentResolver->getFrenchPublicView($page);

        return $this->render('seo/resources-index.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'page' => $content,
            'pageFaqItems' => $this->sitePageFaqParser->parse($content->getBodyHtml()),
            'articles' => $articles,
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
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
    ): Response {
        $page = $this->sitePageRepository->findResourceArticleByPublicSlug($slug);
        if ($page === null) {
            throw $this->createNotFoundException('La ressource demandee n\'existe pas.');
        }

        $related = array_values(array_filter(array_map(
            fn (\App\Entity\SitePage $resourcePage): ?array => $this->buildResourceCard($resourcePage),
            $this->sitePageRepository->findRelatedResourceArticles((string) $page->getSlug(), 4)
        )));

        $content = $this->localizedContentResolver->getFrenchPublicView($page);

        return $this->render('seo/resource-article.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'page' => $content,
            'pageFaqItems' => $this->sitePageFaqParser->parse($content->getBodyHtml()),
            'publicSlug' => $slug,
            'related' => $related,
        ]);
    }

    /**
     * @return array{slug: string, title: string, h1: string, intro: string}|null
     */
    private function buildResourceCard(SitePage $page): ?array
    {
        $content = $this->localizedContentResolver->getFrenchPublicView($page);
        $storedSlug = $content->getSlug();
        if (!str_starts_with($storedSlug, self::RESOURCE_ARTICLE_PREFIX)) {
            return null;
        }

        $publicSlug = substr($storedSlug, strlen(self::RESOURCE_ARTICLE_PREFIX));
        if ($publicSlug === false || $publicSlug === '') {
            return null;
        }

        return [
            'slug' => $publicSlug,
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
