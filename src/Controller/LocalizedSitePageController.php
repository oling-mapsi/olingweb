<?php

namespace App\Controller;

use App\Entity\SitePageTranslation;
use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Service\I18n\LocalizedUrlGenerator;
use App\Service\LocalizedContentResolver;
use App\Service\PublicSitePageResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class LocalizedSitePageController extends AbstractController
{
    public function __construct(
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly LocalizedUrlGenerator $localizedUrlGenerator,
        private readonly PublicSitePageResolver $publicSitePageResolver,
        private readonly PracticeRepository $practiceRepository,
        private readonly ServicesRepository $servicesRepository,
        private readonly ProjetRepository $projetRepository,
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

        $sourceSlug = (string) $page->getSourcePage()->getSlug();
        if ($this->publicSitePageResolver->isExpertisesIndexSource($sourceSlug)) {
            return $this->render('expertises/index.html.twig', [
                'page' => $this->publicSitePageResolver->getExpertisesIndex($_locale),
                'expertisePages' => $this->publicSitePageResolver->getExpertisePages($_locale),
                'practices' => $this->localizePractices($_locale),
                'services' => $this->localizeServices($_locale),
                'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
                'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
            ]);
        }

        $expertiseSlug = $this->publicSitePageResolver->expertisePublicSlugForSource($sourceSlug);
        if ($expertiseSlug !== null) {
            $pages = $this->publicSitePageResolver->getExpertisePages($_locale);

            return $this->render('expertises/show.html.twig', [
                'page' => $pages[$expertiseSlug] ?? $this->publicSitePageResolver->getExpertisePages(SitePageTranslation::LOCALE_FR)[$expertiseSlug],
                'pageSlug' => $expertiseSlug,
                'expertisePages' => $pages,
                'practices' => $this->localizePractices($_locale),
                'services' => $this->localizeServices($_locale),
                'projects' => array_map(fn ($project) => $this->localizedContentResolver->getProjetView($project, $_locale), $this->projetRepository->findBy([], ['id' => 'DESC'], 6)),
                'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
                'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
            ]);
        }

        if ($this->publicSitePageResolver->isSectorsIndexSource($sourceSlug)) {
            return $this->render('sectors/index.html.twig', [
                'page' => $this->publicSitePageResolver->getSectorsIndex($_locale),
                'sectorPages' => $this->publicSitePageResolver->getSectorPages($_locale),
                'sectorCatalog' => $this->publicSitePageResolver->getSectorCatalogEntries($_locale),
                'practices' => $this->localizePractices($_locale),
                'services' => $this->localizeServices($_locale),
                'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
                'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
            ]);
        }

        $sectorSlug = $this->publicSitePageResolver->sectorPublicSlugForSource($sourceSlug);
        if ($sectorSlug !== null) {
            $pages = $this->publicSitePageResolver->getSectorPages($_locale);

            return $this->render('sectors/show.html.twig', [
                'page' => $pages[$sectorSlug] ?? $this->publicSitePageResolver->getSectorPages(SitePageTranslation::LOCALE_FR)[$sectorSlug],
                'pageSlug' => $sectorSlug,
                'expertisePages' => $this->publicSitePageResolver->getExpertisePages($_locale),
                'practices' => $this->localizePractices($_locale),
                'services' => $this->localizeServices($_locale),
                'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
                'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
            ]);
        }

        return $this->render('public/localized_site_page.html.twig', [
            'page' => $page,
            'locale' => $_locale,
            'canonicalPath' => $this->localizedUrlGenerator->sitePagePath($page->getSourcePage(), $_locale),
            'localizedAlternates' => $this->localizedUrlGenerator->sitePageAlternates($page->getSourcePage()),
        ]);
    }

    private function localizePractices(string $locale): array
    {
        return array_map(fn ($practice) => $this->localizedContentResolver->getPracticeView($practice, $locale), $this->practiceRepository->findAll());
    }

    private function localizeServices(string $locale): array
    {
        return array_map(fn ($service) => $this->localizedContentResolver->getServiceView($service, $locale), $this->servicesRepository->findAll());
    }
}
