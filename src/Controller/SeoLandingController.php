<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoLandingController extends AbstractController
{
    #[Route('/rgpd', name: 'seo_rgpd', options: ['sitemap' => true])]
    public function rgpd(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/rgpd.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/cyber-securite', name: 'seo_cyber_securite', options: ['sitemap' => true])]
    public function cyberSecurite(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/cyber-securite.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/conseil-qualite', name: 'seo_conseil_qualite', options: ['sitemap' => true])]
    public function conseilQualite(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-qualite.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/public-pme-eti', name: 'seo_public_pme_eti', options: ['sitemap' => true])]
    public function publicPmeEti(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/public-pme-eti.html.twig', $practiceRepository, $servicesRepository);
    }

    private function renderLanding(
        string $template,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        return $this->render($template, [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
        ]);
    }
}
