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
                'heroTitle' => 'AMOA CRM : cadrer, choisir et réussir votre projet CRM',
                'heroIntro' => 'OLING accompagne les directions commerciales, marketing, service client et SI pour cadrer les processus, structurer les donnees, choisir la bonne solution CRM et piloter un deploiement utile et adopte.',
                'promise' => 'OLING n\'est ni editeur, ni revendeur, ni integrateur CRM. Le cabinet intervient en AMOA CRM pour clarifier les objectifs metier, objectiver les choix, tenir la gouvernance projet et securiser l\'adoption.',
                'positioningTitle' => 'Pourquoi OLING intervient sur un projet CRM',
                'scopeTitle' => 'Ce que couvre une mission AMOA CRM',
                'triggerTitle' => 'Quand lancer ou refondre un CRM',
                'linksTitle' => 'Explorer les sujets lies a un projet CRM',
                'linksIntro' => 'Ces pages prolongent le cadrage CRM avec les applications connexes, les references et les expertises mobilisables selon votre contexte.',
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
                    ['href' => '/amoa-si', 'label' => 'AMOA des systemes d’information', 'description' => 'Pour le cadrage transverse, la gouvernance SI et le pilotage des projets.'],
                    ['href' => '/business-apps/erp', 'label' => 'Projet ERP', 'description' => 'Pour les projets ERP, interfaces, reprise de donnees et pilotage integrateur.'],
                    ['href' => '/gmao', 'label' => 'Projet GMAO', 'description' => 'Pour les contextes maintenance, actifs, mobilite et interventions terrain.'],
                    ['href' => '/si-finance', 'label' => 'SI Finance', 'description' => 'Pour les interfaces finance, reporting, controle de gestion et cloture.'],
                    ['href' => '/projets', 'label' => 'Nos references', 'description' => 'Pour voir des contextes publies d’AMOA SI et d’outillage metier.'],
                    ['href' => '/a-propos/team', 'label' => 'Notre equipe', 'description' => 'Pour identifier les profils OLING mobilisables sur un projet CRM.'],
                    ['href' => '/ressources', 'label' => 'Ressources', 'description' => 'Pour approfondir cadrage, adoption, donnees et risques projet.'],
                ],
                'schemaServiceType' => 'AMOA CRM',
            ],
            'gmao' => [
                'metaTitle' => 'AMOA GMAO : cadrage, choix et déploiement de votre GMAO | OLING',
                'metaDescription' => 'Cabinet AMOA GMAO indépendant : cadrage maintenance, choix de solution, données équipements, pilotage intégrateur, recette, migration et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA GMAO indépendant',
                'heroTitle' => 'AMOA GMAO : cadrer, choisir et réussir votre projet de maintenance',
                'heroIntro' => 'OLING accompagne les directions maintenance, exploitation, patrimoine et SI pour cadrer les processus, structurer les données équipements, choisir la bonne GMAO et piloter un déploiement utile sur le terrain.',
                'promise' => 'OLING n\'est ni éditeur, ni revendeur, ni intégrateur GMAO. Le cabinet intervient en AMOA GMAO pour clarifier les besoins maintenance, objectiver les choix, piloter les arbitrages et sécuriser l\'adoption des équipes terrain.',
                'positioningTitle' => 'Pourquoi OLING intervient sur un projet GMAO',
                'scopeTitle' => 'Ce que couvre une mission AMOA GMAO',
                'triggerTitle' => 'Quand lancer ou refondre une GMAO',
                'linksTitle' => 'Explorer les sujets lies a un projet GMAO',
                'linksIntro' => 'Ces pages prolongent le cadrage GMAO avec la maintenance, les actifs, les references et les expertises mobilisables selon votre contexte.',
                'focus' => [
                    'Cadrage maintenance, actifs, ordres de travail, préventif et indicateurs',
                    'Expression des besoins, cahier des charges et aide au choix GMAO',
                    'Pilotage intégrateur, recette, migration des données et déploiement terrain',
                    'Interfaces ERP, stocks, achats, mobilité et conduite du changement',
                ],
                'missionPhases' => [
                    'Diagnostic de l\'existant, des usages réels et des limites du dispositif maintenance',
                    'Cadrage des processus maintenance, des rôles, des équipements et des données de référence',
                    'Choix de solution, scénarios métier, démonstrations et arbitrage entre éditeurs et intégrateurs',
                    'Pilotage du projet, stratégie de reprise, recette, déploiement terrain et mise sous contrôle de l\'adoption',
                ],
                'deliverables' => [
                    'Diagnostic de l\'existant, cartographie des processus maintenance et roadmap GMAO',
                    'Expression des besoins, cahier des charges et modèle de données équipements',
                    'Grille de choix, scénarios de démonstration et dossier d\'arbitrage',
                    'Stratégie de reprise, stratégie de recette, plan de déploiement et conduite du changement',
                ],
                'projectContexts' => [
                    'GMAO peu utilisée, maintenance encore gérée sous Excel ou processus différents selon les sites',
                    'Refonte GMAO pour mieux piloter actifs, préventif, interventions, stocks et sous-traitance',
                    'Projet maintenance impliquant terrain, méthodes, patrimoine, achats, SI et finance',
                    'Migration sensible, historique incomplet ou besoin de reconnecter GMAO et ERP',
                ],
                'clientTypes' => [
                    'Directions maintenance, exploitation, patrimoine et services techniques',
                    'DSI, responsables applicatifs et chefs de projet transformation',
                    'Industrie, infrastructures, collectivités, patrimoine technique et organisations multisites',
                    'Equipes ayant besoin d\'un tiers indépendant pour arbitrer entre besoins métier, données et intégrateurs',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systèmes d\'information', 'description' => 'Pour le cadrage transverse, la gouvernance SI et le pilotage des transformations.'],
                    ['href' => '/business-apps/erp', 'label' => 'Projet ERP', 'description' => 'Pour les interfaces achats, stocks, finance, référentiels et pilotage intégrateur.'],
                    ['href' => '/crm', 'label' => 'Projet CRM', 'description' => 'Pour les contextes relation client, données, adoption et pilotage applicatif connexe.'],
                    ['href' => '/si-finance', 'label' => 'SI Finance', 'description' => 'Pour les interfaces comptables, coûts de maintenance, reporting et contrôle de gestion.'],
                    ['href' => '/secteurs/industrie', 'label' => 'Industrie et PMI', 'description' => 'Pour les contextes industriels, multi-sites, actifs critiques et performance opérationnelle.'],
                    ['href' => '/projets', 'label' => 'Nos références', 'description' => 'Pour voir des contextes publiés d’AMOA SI et d’outillage métier.'],
                    ['href' => '/a-propos/team', 'label' => 'Notre équipe', 'description' => 'Pour identifier les profils OLING mobilisables sur un projet GMAO.'],
                    ['href' => '/ressources', 'label' => 'Ressources', 'description' => 'Pour approfondir cadrage, données, adoption et risques projet.'],
                ],
                'schemaServiceType' => 'AMOA GMAO',
            ],
            'si-finance' => [
                'metaTitle' => 'AMOA SI Finance : cadrage et pilotage de transformation | OLING',
                'metaDescription' => 'Cabinet AMOA SI Finance indépendant : cadrage, processus Finance, ERP/EPM, reporting, données, choix de solution, pilotage, recette et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA SI Finance indépendant',
                'heroTitle' => 'AMOA SI Finance : cadrer et piloter votre transformation Finance',
                'heroIntro' => 'OLING accompagne les directions Finance et DSI pour cadrer les processus, fiabiliser les données, clarifier les outils ERP ou EPM et piloter une transformation SI Finance utile, gouvernée et déployable.',
                'promise' => 'OLING n\'est ni éditeur EPM, ni intégrateur financier, ni cabinet comptable. Le cabinet intervient en AMOA SI Finance pour objectiver les besoins, sécuriser les choix, tenir la gouvernance projet et coordonner Finance, DSI et intégrateurs.',
                'positioningTitle' => 'Pourquoi OLING intervient sur un projet SI Finance',
                'scopeTitle' => 'Ce que couvre une mission AMOA SI Finance',
                'triggerTitle' => 'Quand lancer ou refondre un SI Finance',
                'linksTitle' => 'Explorer les sujets lies a un projet SI Finance',
                'linksIntro' => 'Ces pages prolongent le cadrage SI Finance avec l’ERP, la facturation electronique, les references et les expertises mobilisables selon votre contexte.',
                'focus' => [
                    'Diagnostic des processus Finance, des irritants de cloture, de reporting et de controle de gestion',
                    'Expression des besoins, cadrage cible et aide au choix pour ERP Finance, EPM ou outils satellites',
                    'Qualite des donnees, referentiels, interfaces, reprises et regles de gestion',
                    'Pilotage du projet, recette, conduite du changement et coordination Finance, DSI et integrateur',
                ],
                'missionPhases' => [
                    'Diagnostic de l\'existant, des flux P2P, O2C, R2R et des points de fragilite qui ralentissent cloture et pilotage',
                    'Cadrage des besoins, des processus Finance, des responsabilites, des donnees et de la gouvernance cible',
                    'Choix de solution, evaluation des scenarios ERP ou EPM, demonstrations, criteres et arbitrages',
                    'Pilotage du projet, strategie de reprise, recette, deploiement et mise sous controle de l\'adoption par la fonction Finance',
                ],
                'deliverables' => [
                    'Diagnostic SI Finance, cartographie des processus et note de cadrage',
                    'Expression des besoins, roadmap, architecture fonctionnelle cible et gouvernance projet',
                    'Grille de choix, matrice de scoring et dossier d\'arbitrage entre solutions et integrateurs',
                    'Strategie de reprise, strategie de recette, plan de conduite du changement et indicateurs de pilotage',
                ],
                'projectContexts' => [
                    'Cloture longue, reporting peu fiable, multiplication des fichiers Excel et outils fragmentes',
                    'Projet ERP Finance, EPM, consolidation ou reporting avec besoin de clarifier le perimetre et les priorites',
                    'Transformation de la fonction Finance impliquant DAF, controle de gestion, comptabilite, tresorerie et DSI',
                    'Migration sensible, interfaces fragiles, qualite de donnees insuffisante ou besoin de reprendre une trajectoire projet',
                ],
                'clientTypes' => [
                    'Directions Financieres, controle de gestion, comptabilite, tresorerie et fonctions support',
                    'DSI, responsables applicatifs et chefs de projet transformation',
                    'PME, ETI, groupes multisites, secteur public et organisations avec enjeux de gouvernance et reporting',
                    'Equipes ayant besoin d\'un tiers independant pour arbitrer entre processus, donnees, ERP, EPM et integrateurs',
                ],
                'anonymousProofsTitle' => 'Mission anonymisee associee',
                'anonymousProofsIntro' => 'Cette preuve reste anonymisee et limitee au perimetre SI Finance documente.',
                'anonymousProofs' => [
                    [
                        'label' => 'Grand port maritime',
                        'sector' => 'Transport portuaire',
                        'mission' => 'AMOA de transformation du SI Finance : cadrage, contraintes reglementaires et preparation de la reprise de donnees.',
                        'perimeter' => ['SI Finance', 'contraintes reglementaires', 'reprise de donnees'],
                    ],
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systemes d\'information', 'description' => 'Pour le cadrage transverse, la gouvernance SI et le pilotage des projets.'],
                    ['href' => '/business-apps/erp', 'label' => 'Projet ERP', 'description' => 'Pour les projets ERP globaux, interfaces, reprises de donnees et pilotage integrateur.'],
                    ['href' => '/facturation-electronique-amoa', 'label' => 'Facturation electronique AMOA', 'description' => 'Pour les chantiers e-invoicing et e-reporting lies a la transformation Finance.'],
                    ['href' => '/projets', 'label' => 'Nos references', 'description' => 'Pour voir des contextes publies d’AMOA SI, de gouvernance et d’outillage metier.'],
                    ['href' => '/a-propos/team', 'label' => 'Notre equipe', 'description' => 'Pour identifier les profils OLING mobilisables sur un projet SI Finance.'],
                    ['href' => '/ressources', 'label' => 'Ressources', 'description' => 'Pour approfondir cadrage, donnees, pilotage, conformite et transformation.'],
                ],
                'schemaServiceType' => 'AMOA SI Finance',
            ],
            'conformite-reglementaire' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'Conformite reglementaire | IA Act, ISO, QSE, energie et securite | OLING',
                'metaDescription' => 'Conformite reglementaire executable : IA Act, RGPD, ISO, cybersecurite, qualite, environnement, energie, DORA, NIS2 et dispositifs de preuve.',
                'heroBadge' => 'Conformité',
                'heroTitle' => 'Conformite reglementaire : convertir obligations en execution',
                'heroIntro' => 'RGPD, cyber, NIS2, DORA, IA ou ISO mobilisent souvent plusieurs directions. OLING structure les obligations, responsabilités, preuves et plans d’action à piloter.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'Obligations RGPD, cyber, NIS2, DORA, IA ou ISO traitées dans des chantiers séparés.',
                    'Preuves d’audit dispersées entre directions métier, DSI, qualité et conformité.',
                    'Plan d’actions réglementaire difficile à prioriser ou à suivre en comité.',
                    'Projet SI critique qui doit intégrer des exigences de conformité dès le cadrage.',
                ],
                'missionPhases' => [
                    'Cartographier les obligations, les risques associés et les responsables.',
                    'Prioriser les actions selon criticité, effort, échéance et impact métier.',
                    'Structurer les preuves attendues pour les contrôles internes, audits ou revues de direction.',
                    'Relier conformité, projets SI, cybersécurité, données et qualité.',
                ],
                'deliverables' => [
                    'Cartographie obligations / risques / responsables.',
                    'Plan d’actions priorisé.',
                    'RACI et calendrier de revues.',
                    'Tableau de bord de suivi pour comité de direction.',
                ],
                'supportLinks' => [
                    ['href' => '/expertises-audit/rgpd', 'label' => 'RGPD et DPO'],
                    ['href' => '/cyber-securite', 'label' => 'Cybersécurité'],
                    ['href' => '/conseil-qualite', 'label' => 'Conseil qualité'],
                ],
                'finalCtaLabel' => 'Parler à un consultant conformité',
                'finalCtaTitle' => 'Qualifier les obligations à piloter',
                'finalCtaText' => null,
                'schemaServiceType' => 'Conformité réglementaire',
            ],
            'direction-qualite-deleguee' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'Direction qualite deleguee | Pilotage ISO, audits et performance | OLING',
                'metaDescription' => 'Direction qualite deleguee : pilotage du systeme qualite, audits, indicateurs, plans d action et coordination operationnelle.',
                'heroBadge' => 'Qualité',
                'heroTitle' => 'Direction qualite deleguee : gouvernance, audits et amelioration continue',
                'heroIntro' => 'OLING intervient comme appui de direction qualité lorsque l’organisation doit structurer ses rituels, ses audits et ses plans d’amélioration sans recruter immédiatement une fonction senior.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'Fonction qualité absente, vacante ou trop sollicitée.',
                    'Audits, non-conformités et plans d’actions suivis de manière discontinue.',
                    'Indicateurs qualité difficiles à relier aux processus opérationnels.',
                    'Besoin de cadencer la gouvernance qualité avec les directions métier.',
                ],
                'missionPhases' => [
                    'Installer les rituels de pilotage qualité et les responsabilités.',
                    'Préparer ou coordonner les audits internes et les plans de correction.',
                    'Suivre les non-conformités, actions, échéances et arbitrages.',
                    'Faire le lien entre qualité, processus, risques et performance opérationnelle.',
                ],
                'deliverables' => [
                    'Plan de pilotage qualité.',
                    'Tableau de suivi des audits, actions et responsables.',
                    'Indicateurs qualité pour comité de direction.',
                    'Compte rendu de revue qualité.',
                ],
                'supportLinks' => [
                    ['href' => '/conseil-qualite', 'label' => 'Conseil qualité'],
                    ['href' => '/gestion-risques-audit-controle-interne', 'label' => 'Risques, audit et contrôle interne'],
                ],
                'finalCtaLabel' => 'Parler direction qualité',
                'finalCtaTitle' => 'Besoin de structurer une fonction qualité sans poste dédié ?',
                'finalCtaText' => null,
                'schemaServiceType' => 'Direction qualité déléguée',
            ],
            'direction-conformite-externalisee' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'Direction conformite externalisee | IA Act, RGPD, ISO, cyber | OLING',
                'metaDescription' => 'Direction conformite externalisee : gouvernance reglementaire, plans de mise en conformite, suivi des obligations IA Act RGPD ISO cyber et reporting direction.',
                'heroBadge' => 'Conformité',
                'heroTitle' => 'Direction conformite externalisee : gouvernance et execution reglementaire',
                'heroIntro' => 'OLING structure le pilotage conformité lorsque les obligations existent déjà mais que les responsabilités, les preuves et les décisions ne sont pas suffisamment suivies.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'Obligations réglementaires nombreuses et portées par plusieurs directions.',
                    'Absence de fonction conformité senior ou besoin de renfort temporaire.',
                    'Audits, contrôles ou demandes clients nécessitant des preuves suivies.',
                    'Programme RGPD, IA, ISO ou cyber à remettre sous gouvernance.',
                ],
                'missionPhases' => [
                    'Prioriser les obligations et formaliser les responsables.',
                    'Installer les tableaux de bord, comités et circuits de décision.',
                    'Suivre les plans d’actions et les preuves associées.',
                    'Coordonner DPO, RSSI, qualité, DSI, juridique et métiers selon le sujet.',
                ],
                'deliverables' => [
                    'Cartographie des obligations et risques.',
                    'Plan 90 jours puis feuille de route.',
                    'Tableau de bord conformité.',
                    'Dossier de preuves pour contrôles ou audits.',
                ],
                'supportLinks' => [
                    ['href' => '/conformite-reglementaire', 'label' => 'Conformité réglementaire'],
                    ['href' => '/expertises-audit/rgpd', 'label' => 'RGPD et DPO'],
                    ['href' => '/cyber-securite', 'label' => 'Cybersécurité'],
                ],
                'finalCtaLabel' => 'Activer une direction conformité',
                'finalCtaTitle' => 'Renfort temporaire ou direction conformité externalisée ?',
                'finalCtaText' => null,
                'schemaServiceType' => 'Direction conformité externalisée',
            ],
            'dsi-externalisee' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'DSI externalisee | Gouvernance SI, performance et securite | OLING',
                'metaDescription' => 'DSI externalisee : feuille de route SI, gouvernance fournisseurs, qualite de service, securite et conformite pour organisations publiques et privees.',
                'heroBadge' => 'DSI externalisée',
                'heroTitle' => 'DSI externalisee : piloter, securiser et transformer le SI',
                'heroIntro' => 'OLING intervient en appui de direction SI pour cadrer les priorités, piloter les fournisseurs, suivre les risques et donner un cadre de décision au portefeuille SI.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'DSI absente, vacante ou sous-dimensionnée par rapport aux projets en cours.',
                    'Portefeuille projets SI difficile à prioriser avec les directions métier.',
                    'Fournisseurs, SLA, budget ou risques SI insuffisamment pilotés.',
                    'Besoin d’une direction SI de transition avant recrutement ou réorganisation.',
                ],
                'missionPhases' => [
                    'Structurer la gouvernance SI, les rituels et les arbitrages.',
                    'Prioriser le portefeuille projets selon criticité, coût, risque et capacité.',
                    'Piloter fournisseurs, contrats, niveaux de service et plans d’action.',
                    'Suivre budget, dette technique, continuité, sécurité et dépendances.',
                ],
                'deliverables' => [
                    'Feuille de route SI et portefeuille priorisé.',
                    'Tableau de bord direction SI.',
                    'Plan de risques SI.',
                    'Cadre de gouvernance fournisseurs et SLA.',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA SI'],
                    ['href' => '/infrastructure-si-amoa', 'label' => 'AMOA infrastructure SI'],
                ],
                'finalCtaLabel' => 'Parler DSI externalisée',
                'finalCtaTitle' => 'Renforcer temporairement la direction SI',
                'finalCtaText' => null,
                'schemaServiceType' => 'DSI externalisée',
            ],
            'infrastructure-si-amoa' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'AMOA infrastructure SI | Cloud, resilience et securite | OLING',
                'metaDescription' => 'AMOA infrastructure SI : trajectoires cloud et hybride, modernisation des infrastructures critiques, continuite d activite, securite et conformite.',
                'heroBadge' => 'Infrastructure SI',
                'heroTitle' => 'AMOA infrastructure SI : fiabiliser les plateformes critiques',
                'heroIntro' => 'OLING intervient côté maîtrise d’ouvrage pour cadrer les choix d’architecture, les migrations et la modernisation des plateformes critiques, sans se substituer à l’intégrateur ou à l’infogérant.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'Infrastructure vieillissante, dette technique ou obsolescence à prioriser.',
                    'Migration cloud, hybride ou hébergement à cadrer avant consultation.',
                    'Exigences de disponibilité, performance, cybersécurité ou PCA/PRA à formaliser.',
                    'Projet infrastructure qui impacte ERP, finance, GMAO ou flux critiques.',
                ],
                'missionPhases' => [
                    'Qualifier les besoins métier, contraintes techniques et dépendances applicatives.',
                    'Comparer les scénarios d’architecture et de migration.',
                    'Formaliser les exigences de service, sécurité, continuité et exploitation.',
                    'Piloter les arbitrages entre coûts, risques, délais et capacité interne.',
                ],
                'deliverables' => [
                    'Note de cadrage infrastructure.',
                    'Scénarios d’architecture et trajectoire de migration.',
                    'Matrice risques / coûts / dépendances.',
                    'Exigences de disponibilité, sécurité et continuité.',
                ],
                'supportLinks' => [
                    ['href' => '/cyber-securite', 'label' => 'Cybersécurité'],
                    ['href' => '/dsi-externalisee', 'label' => 'DSI externalisée'],
                    ['href' => '/amoa-si', 'label' => 'AMOA SI'],
                ],
                'finalCtaLabel' => 'Lancer un cadrage infrastructure',
                'finalCtaTitle' => 'Cadrer une évolution infrastructure',
                'finalCtaText' => null,
                'schemaServiceType' => 'AMOA infrastructure SI',
            ],
            'conseil-qualite' => [
                'compactLanding' => true,
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'showFaq' => false,
                'metaTitle' => 'Conseil qualite | Processus, ISO et amelioration continue | OLING',
                'metaDescription' => 'Conseil qualite pour secteur public, PME et ETI : gouvernance des processus, demarches ISO, indicateurs de performance et amelioration continue.',
                'heroBadge' => 'Conseil qualité',
                'heroTitle' => 'Conseil qualite : fiabiliser les processus et la performance durable',
                'heroIntro' => 'OLING intervient sur les processus, indicateurs, audits et plans d’amélioration lorsque la qualité doit devenir pilotable dans les opérations.',
                'scopeTitle' => 'Quand cette intervention est utile',
                'triggerTitle' => 'Ce qu’OLING réalise',
                'deliverablesTitle' => 'Documents remis',
                'linksTitle' => 'Pages liées',
                'focus' => [
                    'Processus critiques documentés mais peu appliqués ou différents selon les équipes.',
                    'Indicateurs qualité nombreux mais peu utiles à la décision.',
                    'Audits internes, plans d’actions ou revues qualité à remettre sous contrôle.',
                    'Démarche ISO ou QSE à relier aux activités et aux outils SI.',
                ],
                'missionPhases' => [
                    'Cartographier les processus et les points de contrôle utiles.',
                    'Simplifier les indicateurs qualité et les rituels de pilotage.',
                    'Structurer les plans d’amélioration, responsables et échéances.',
                    'Faire le lien entre qualité, conformité, risques et projets SI.',
                ],
                'deliverables' => [
                    'Cartographie de processus.',
                    'Plan d’amélioration priorisé.',
                    'Tableau de bord qualité.',
                    'Support de revue qualité ou comité de pilotage.',
                ],
                'supportLinks' => [
                    ['href' => '/expertises-audit/qse', 'label' => 'QSE'],
                    ['href' => '/conformite-reglementaire', 'label' => 'Conformité réglementaire'],
                ],
                'finalCtaLabel' => 'Échanger sur ma démarche QSE',
                'finalCtaTitle' => 'Rendre la démarche qualité plus opérationnelle',
                'finalCtaText' => null,
                'schemaServiceType' => 'Conseil qualité',
            ],
            default => [],
        };
    }
}
