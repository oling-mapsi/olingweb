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

    #[Route('/erp-progiciel', name: 'seo_erp_progiciel', options: ['sitemap' => true])]
    public function erpProgiciel(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/erp-progiciel.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/gmao', name: 'seo_gmao', options: ['sitemap' => true])]
    public function gmao(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/gmao.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/crm', name: 'seo_crm', options: ['sitemap' => true])]
    public function crm(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/crm.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/mapsi-progiciel', name: 'seo_mapsi_progiciel', options: ['sitemap' => true])]
    public function mapsiProgiciel(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/mapsi-progiciel.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/hexagone-drom-dom-tom', name: 'seo_hexagone_drom', options: ['sitemap' => true])]
    public function hexagoneDrom(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/hexagone-drom.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/gestion-risques-audit-controle-interne', name: 'seo_risques_audit', options: ['sitemap' => true])]
    public function risquesAudit(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/risques-audit.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/direction-qualite-deleguee', name: 'seo_direction_qualite_deleguee', options: ['sitemap' => true])]
    public function directionQualiteDeleguee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/direction-qualite-deleguee.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/direction-conformite-externalisee', name: 'seo_direction_conformite_externalisee', options: ['sitemap' => true])]
    public function directionConformiteExternalisee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/direction-conformite-externalisee.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/dsi-externalisee', name: 'seo_dsi_externalisee', options: ['sitemap' => true])]
    public function dsiExternalisee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/dsi-externalisee.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-paris', name: 'seo_conseil_paris', options: ['sitemap' => true])]
    public function conseilParis(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-paris.html.twig', $practiceRepository, $servicesRepository);
    }

    #[Route('/metropoles-hexagone', name: 'seo_metropoles_hexagone', options: ['sitemap' => true])]
    public function metropolesHexagone(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/metropoles-hexagone.html.twig', $practiceRepository, $servicesRepository);
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
