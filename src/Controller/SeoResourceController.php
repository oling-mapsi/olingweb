<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\SitePageRepository;
use App\Repository\ServicesRepository;
use App\Service\SitePageFaqParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoResourceController extends AbstractController
{
    private const RESOURCE_ARTICLE_PREFIX = 'ressource-';

    public function __construct(
        private SitePageRepository $sitePageRepository,
        private SitePageFaqParser $sitePageFaqParser
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

        return $this->render('seo/resources-index.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'page' => $page,
            'pageFaqItems' => $this->sitePageFaqParser->parse($page->getBodyHtml()),
            'articles' => $articles,
        ]);
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

        return $this->render('seo/resource-article.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'page' => $page,
            'pageFaqItems' => $this->sitePageFaqParser->parse($page->getBodyHtml()),
            'publicSlug' => $slug,
            'related' => $related,
        ]);
    }

    /**
     * @return array{slug: string, title: string, h1: string, intro: string}|null
     */
    private function buildResourceCard(\App\Entity\SitePage $page): ?array
    {
        $storedSlug = (string) $page->getSlug();
        if (!str_starts_with($storedSlug, self::RESOURCE_ARTICLE_PREFIX)) {
            return null;
        }

        $publicSlug = substr($storedSlug, strlen(self::RESOURCE_ARTICLE_PREFIX));
        if ($publicSlug === false || $publicSlug === '') {
            return null;
        }

        return [
            'slug' => $publicSlug,
            'title' => (string) $page->getTitle(),
            'h1' => (string) ($page->getHeroTitle() ?: $page->getTitle()),
            'intro' => (string) ($page->getHeroIntro() ?: ''),
        ];
    }
}
