<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Service\PublicSitePageResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PublicSiteController extends AbstractController
{
    public function __construct(private readonly PublicSitePageResolver $publicSitePageResolver)
    {
    }

    #[Route('/expertises', name: 'expertises_index', options: ['sitemap' => true])]
    public function expertisesIndex(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        return $this->render('expertises/index.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'page' => $this->publicSitePageResolver->getExpertisesIndex(),
            'expertisePages' => $this->publicSitePageResolver->getExpertisePages(),
        ]);
    }

    #[Route('/expertises/{slug}', name: 'expertises_show')]
    public function expertisesShow(
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        ProjetRepository $projetRepository
    ): Response {
        $pages = $this->publicSitePageResolver->getExpertisePages();
        $page = $pages[$slug] ?? null;

        if ($page === null) {
            throw $this->createNotFoundException('Expertise introuvable.');
        }

        return $this->render('expertises/show.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'projects' => $projetRepository->findBy([], ['id' => 'DESC'], 6),
            'page' => $page,
            'pageSlug' => $slug,
            'expertisePages' => $pages,
        ]);
    }

    #[Route('/secteurs', name: 'sectors_index', options: ['sitemap' => true])]
    public function sectorsIndex(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $page = $this->publicSitePageResolver->getSectorsIndex();
        $sectorPages = $this->publicSitePageResolver->getSectorPages();

        return $this->render('sectors/index.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'page' => $page,
            'sectorPages' => $sectorPages,
        ]);
    }

    #[Route('/secteurs/{slug}', name: 'sectors_show')]
    public function sectorsShow(
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $pages = $this->publicSitePageResolver->getSectorPages();
        $page = $pages[$slug] ?? null;

        if ($page === null) {
            throw $this->createNotFoundException('Secteur introuvable.');
        }

        return $this->render('sectors/show.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'page' => $page,
            'pageSlug' => $slug,
            'expertisePages' => $this->publicSitePageResolver->getExpertisePages(),
        ]);
    }
}
