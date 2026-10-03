<?php

namespace App\Controller;

use App\Entity\SitePageTranslation;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\LocalizedContentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LocalizedSitePageController extends AbstractController
{
    public function __construct(
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly LocalizedUrlGenerator $localizedUrlGenerator,
    ) {
    }

    #[Route('/{_locale}/{slug}', name: 'localized_site_page_show', requirements: ['_locale' => 'en|es', 'slug' => '[a-z0-9][a-z0-9\-]*'], methods: ['GET'], priority: 50)]
    public function show(Request $request, string $_locale, string $slug): Response
    {
        SitePageTranslation::assertSupportedLocale($_locale);
        $request->setLocale($_locale);

        $page = $this->localizedContentResolver->getPublishedPublicViewByLocalizedSlug($_locale, $slug);
        if ($page === null) {
            throw $this->createNotFoundException('Localized page not found.');
        }

        if ($page->getSourcePage()->getSlug() === 'home') {
            return $this->redirect($this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale) ?? '/'.$_locale, Response::HTTP_MOVED_PERMANENTLY);
        }

        if ($page->getSourcePage()->getSlug() === 'ressources' || str_starts_with((string) $page->getSourcePage()->getSlug(), 'ressource-')) {
            return $this->redirect($this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale) ?? '/'.$_locale, Response::HTTP_MOVED_PERMANENTLY);
        }

        return $this->render('public/localized_site_page.html.twig', [
            'page' => $page,
            'locale' => $_locale,
            'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
            'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
        ]);
    }
}
