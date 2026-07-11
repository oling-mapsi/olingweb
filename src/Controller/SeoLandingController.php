<?php

namespace App\Controller;

use App\Repository\SitePageRepository;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Service\SeoGeoInternalLinkService;
use App\Service\SitePageFaqParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoLandingController extends AbstractController
{
    public function __construct(
        private SitePageRepository $sitePageRepository,
        private SitePageFaqParser $sitePageFaqParser,
        private SeoGeoInternalLinkService $seoGeoInternalLinkService
    ) {
    }

    #[Route('/rgpd', name: 'seo_rgpd', options: ['sitemap' => true])]
    public function rgpd(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/rgpd.html.twig', 'rgpd', $practiceRepository, $servicesRepository);
    }

    #[Route('/cyber-securite', name: 'seo_cyber_securite', options: ['sitemap' => true])]
    public function cyberSecurite(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/cyber-securite.html.twig', 'cyber-securite', $practiceRepository, $servicesRepository);
    }

    #[Route('/conseil-qualite', name: 'seo_conseil_qualite', options: ['sitemap' => true])]
    public function conseilQualite(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-qualite.html.twig', 'conseil-qualite', $practiceRepository, $servicesRepository);
    }

    #[Route('/public-pme-eti', name: 'seo_public_pme_eti', options: ['sitemap' => true])]
    public function publicPmeEti(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/public-pme-eti.html.twig', 'public-pme-eti', $practiceRepository, $servicesRepository);
    }

    #[Route('/erp-progiciel', name: 'seo_erp_progiciel', options: ['sitemap' => true])]
    public function erpProgiciel(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/erp-progiciel.html.twig', 'erp-progiciel', $practiceRepository, $servicesRepository);
    }

    #[Route('/gmao', name: 'seo_gmao', options: ['sitemap' => true])]
    public function gmao(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/gmao.html.twig', 'gmao', $practiceRepository, $servicesRepository);
    }

    #[Route('/crm', name: 'seo_crm', options: ['sitemap' => true])]
    public function crm(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/crm.html.twig', 'crm', $practiceRepository, $servicesRepository);
    }

    #[Route('/mapsi-progiciel', name: 'seo_mapsi_progiciel', options: ['sitemap' => true])]
    public function mapsiProgiciel(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/mapsi-progiciel.html.twig', 'mapsi-progiciel', $practiceRepository, $servicesRepository);
    }

    #[Route('/hexagone-drom-dom-tom', name: 'seo_hexagone_drom', options: ['sitemap' => true])]
    public function hexagoneDrom(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/hexagone-drom.html.twig', 'hexagone-drom-dom-tom', $practiceRepository, $servicesRepository);
    }

    #[Route('/gestion-risques-audit-controle-interne', name: 'seo_risques_audit', options: ['sitemap' => true])]
    public function risquesAudit(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/risques-audit.html.twig', 'gestion-risques-audit-controle-interne', $practiceRepository, $servicesRepository);
    }

    #[Route('/direction-qualite-deleguee', name: 'seo_direction_qualite_deleguee', options: ['sitemap' => true])]
    public function directionQualiteDeleguee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/direction-qualite-deleguee.html.twig', 'direction-qualite-deleguee', $practiceRepository, $servicesRepository);
    }

    #[Route('/direction-conformite-externalisee', name: 'seo_direction_conformite_externalisee', options: ['sitemap' => true])]
    public function directionConformiteExternalisee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/direction-conformite-externalisee.html.twig', 'direction-conformite-externalisee', $practiceRepository, $servicesRepository);
    }

    #[Route('/dsi-externalisee', name: 'seo_dsi_externalisee', options: ['sitemap' => true])]
    public function dsiExternalisee(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/dsi-externalisee.html.twig', 'dsi-externalisee', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-paris', name: 'seo_conseil_paris', options: ['sitemap' => true])]
    public function conseilParis(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-paris.html.twig', 'cabinet-conseil-paris', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-lyon', name: 'seo_conseil_lyon', options: ['sitemap' => true])]
    public function conseilLyon(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-lyon.html.twig', 'cabinet-conseil-lyon', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-toulouse', name: 'seo_conseil_toulouse', options: ['sitemap' => true])]
    public function conseilToulouse(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-toulouse.html.twig', 'cabinet-conseil-toulouse', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-montpellier', name: 'seo_conseil_montpellier', options: ['sitemap' => true])]
    public function conseilMontpellier(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-montpellier.html.twig', 'cabinet-conseil-montpellier', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-nantes', name: 'seo_conseil_nantes', options: ['sitemap' => true])]
    public function conseilNantes(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-nantes.html.twig', 'cabinet-conseil-nantes', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-bordeaux', name: 'seo_conseil_bordeaux', options: ['sitemap' => true])]
    public function conseilBordeaux(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-bordeaux.html.twig', 'cabinet-conseil-bordeaux', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-guadeloupe', name: 'seo_conseil_guadeloupe', options: ['sitemap' => true])]
    public function conseilGuadeloupe(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-guadeloupe.html.twig', 'cabinet-conseil-guadeloupe', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-martinique', name: 'seo_conseil_martinique', options: ['sitemap' => true])]
    public function conseilMartinique(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-martinique.html.twig', 'cabinet-conseil-martinique', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-reunion', name: 'seo_conseil_reunion', options: ['sitemap' => true])]
    public function conseilReunion(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-reunion.html.twig', 'cabinet-conseil-reunion', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-guyane', name: 'seo_conseil_guyane', options: ['sitemap' => true])]
    public function conseilGuyane(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-guyane.html.twig', 'cabinet-conseil-guyane', $practiceRepository, $servicesRepository);
    }

    #[Route('/cabinet-conseil-saint-pierre-et-miquelon', name: 'seo_conseil_spm', options: ['sitemap' => true])]
    public function conseilSpm(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conseil-spm.html.twig', 'cabinet-conseil-saint-pierre-et-miquelon', $practiceRepository, $servicesRepository);
    }

    #[Route('/metropoles-hexagone', name: 'seo_metropoles_hexagone', options: ['sitemap' => true])]
    public function metropolesHexagone(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/metropoles-hexagone.html.twig', 'metropoles-hexagone', $practiceRepository, $servicesRepository);
    }

    #[Route('/conformite-reglementaire', name: 'seo_conformite_reglementaire', options: ['sitemap' => true])]
    public function conformiteReglementaire(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/conformite-reglementaire.html.twig', 'conformite-reglementaire', $practiceRepository, $servicesRepository);
    }

    #[Route('/si-finance', name: 'seo_si_finance', options: ['sitemap' => true])]
    public function siFinance(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/si-finance.html.twig', 'si-finance', $practiceRepository, $servicesRepository);
    }

    #[Route('/facturation-electronique-amoa', name: 'seo_facturation_electronique_amoa', options: ['sitemap' => true])]
    public function facturationElectroniqueAmoa(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/facturation-electronique-amoa.html.twig', 'facturation-electronique-amoa', $practiceRepository, $servicesRepository);
    }

    #[Route('/infrastructure-si-amoa', name: 'seo_infrastructure_si_amoa', options: ['sitemap' => true])]
    public function infrastructureSiAmoa(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->renderLanding('seo/infrastructure-si-amoa.html.twig', 'infrastructure-si-amoa', $practiceRepository, $servicesRepository);
    }

    private function renderLanding(
        string $template,
        string $pageSlug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $page = $this->sitePageRepository->findOneBy(['slug' => $pageSlug]);
        $pageFaqItems = $this->sitePageFaqParser->parse($page?->getBodyHtml());

        return $this->render($template, [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'page' => $page,
            'pageFaqItems' => $pageFaqItems,
            'zoneMaillage' => $this->seoGeoInternalLinkService->build($page?->getSlug()),
            'zoneExpertises' => $this->seoGeoInternalLinkService->buildExpertiseLinksForZone($page?->getSlug(), 10),
            'pract' => '',
        ]);
    }
}
