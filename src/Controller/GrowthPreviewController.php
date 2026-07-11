<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Repository\SitePageRepository;
use App\Repository\SitePageRevisionRepository;
use App\Service\GrowthPreviewSigner;
use App\Service\GrowthPublishingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class GrowthPreviewController extends AbstractController
{
    #[Route('/preview/ressources/{externalId}/{revisionId}', name: 'growth_preview_article', methods: ['GET'])]
    public function article(
        string $externalId,
        int $revisionId,
        Request $request,
        GrowthPreviewSigner $previewSigner,
        SitePageRevisionRepository $revisionRepository,
        GrowthPublishingService $publishingService,
        SitePageRepository $sitePageRepository,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
    ): Response {
        $expires = (int) $request->query->get('expires', 0);
        $signature = (string) $request->query->get('signature', '');

        if (!$previewSigner->isValid($externalId, $revisionId, $expires, $signature)) {
            throw new AccessDeniedHttpException('Invalid preview signature.');
        }

        $revision = $revisionRepository->find($revisionId);
        if ($revision === null || $revision->getSitePage()?->getExternalId() !== $externalId) {
            throw new NotFoundHttpException('Preview revision not found.');
        }

        $page = $publishingService->buildPreviewView($revision);
        $related = array_values(array_filter(array_map(
            fn (\App\Entity\SitePage $resourcePage): ?array => $this->buildResourceCard($resourcePage),
            $sitePageRepository->findRelatedResourceArticles((string) $revision->getSitePage()?->getSlug(), 4)
        )));

        $response = $this->render('seo/resource-article.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'page' => $page,
            'pageFaqItems' => [],
            'publicSlug' => $revision->getSlug(),
            'related' => $related,
            'isPreview' => true,
        ]);
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

        return $response;
    }

    private function buildResourceCard(\App\Entity\SitePage $page): ?array
    {
        $storedSlug = (string) $page->getSlug();
        if (!str_starts_with($storedSlug, 'ressource-')) {
            return null;
        }

        $publicSlug = substr($storedSlug, strlen('ressource-'));
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
