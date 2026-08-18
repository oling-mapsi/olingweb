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
            'landingNarrative' => $this->getLandingNarrative($pageSlug),
            'pract' => '',
        ]);
    }

    private function getLandingNarrative(string $pageSlug): array
    {
        return match ($pageSlug) {
            'crm' => [
                'metaTitle' => 'AMOA CRM : cadrage, choix et déploiement de votre CRM | OLING',
                'metaDescription' => 'Cabinet AMOA CRM indépendant : cadrage métier, choix de solution, pilotage intégrateur, données, migration, recette et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA CRM independant',
                'heroTitle' => 'AMOA CRM : cadrer, choisir et reussir votre projet CRM',
                'heroIntro' => 'OLING accompagne les directions commerciales, marketing, service client et SI pour cadrer les processus, structurer les donnees, choisir la bonne solution CRM et piloter un deploiement utile et adopte.',
                'promise' => 'OLING n\'est ni editeur, ni revendeur, ni integrateur CRM. Le cabinet intervient en AMOA CRM pour clarifier les objectifs metier, objectiver les choix, tenir la gouvernance projet et securiser l\'adoption.',
                'focus' => [
                    'Cadrage metier, parcours client, processus commerciaux et reporting',
                    'Expression des besoins, cahier des charges et aide au choix CRM',
                    'Pilotage integrateur, recette, migration des donnees et deploiement',
                    'Conduite du changement, adoption et mesure de performance du CRM',
                ],
                'missionPhases' => [
                    'Diagnostic du CRM existant, des usages reels et des limites d\'adoption',
                    'Cadrage des objectifs, des processus, des roles et de la gouvernance des donnees',
                    'Choix de solution, evaluation des scenarios et arbitrage entre editeurs et integrateurs',
                    'Pilotage de projet, strategie de migration, recette, formation et mise sous controle de l’adoption',
                ],
                'deliverables' => [
                    'Note de cadrage, roadmap CRM et gouvernance projet',
                    'Cartographie des processus, expression des besoins et backlog metier',
                    'Grille de choix, matrice de scoring et dossier d\'arbitrage',
                    'Plan de reprise des donnees, strategie de recette et plan de conduite du changement',
                ],
                'projectContexts' => [
                    'CRM peu adopte, usages heterogenes ou multiplication des fichiers Excel paralleles',
                    'Refonte CRM pour mieux piloter prospection, opportunites, comptes, contacts et service client',
                    'Projet CRM impliquant marketing, commerce, service client, data et SI',
                    'Migration sensible, reprise de donnees complexe ou besoin de reprendre la trajectoire projet',
                ],
                'clientTypes' => [
                    'Directions commerciales, marketing et relation client',
                    'DSI, responsables applicatifs et chefs de projet transformation',
                    'PME, ETI, services B2B, organisations multisites et acteurs publics',
                    'Equipes ayant besoin d’un tiers independant pour arbitrer entre besoins metier, donnees et integrateurs',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systemes d’information', 'description' => 'Pour le cadrage transverse, la gouvernance SI et le pilotage des transformations.'],
                    ['href' => '/business-apps/erp', 'label' => 'Projet ERP', 'description' => 'Pour les projets ERP, interfaces, reprise de donnees et pilotage integrateur.'],
                    ['href' => '/gmao', 'label' => 'Projet GMAO', 'description' => 'Pour les contextes maintenance, actifs, mobilite et interventions terrain.'],
                    ['href' => '/si-finance', 'label' => 'SI Finance', 'description' => 'Pour les interfaces finance, reporting, controle de gestion et cloture.'],
                    ['href' => '/projets', 'label' => 'Nos references', 'description' => 'Pour voir des contextes publies de transformation SI, AMOA et outillage metier.'],
                    ['href' => '/a-propos/team', 'label' => 'Notre equipe', 'description' => 'Pour identifier les profils OLING mobilisables sur un projet CRM.'],
                    ['href' => '/ressources', 'label' => 'Ressources', 'description' => 'Pour approfondir cadrage, adoption, donnees et risques de transformation.'],
                ],
                'schemaServiceType' => 'AMOA CRM',
            ],
            default => [],
        };
    }
}
