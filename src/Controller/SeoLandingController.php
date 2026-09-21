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
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'metaTitle' => 'AMOA CRM : cadrage, choix et déploiement de votre CRM | OLING',
                'metaDescription' => 'Cabinet AMOA CRM indépendant : cadrage métier, choix de solution, pilotage intégrateur, données, migration, recette et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA CRM indépendant',
                'heroTitle' => 'AMOA CRM : cadrer, choisir et réussir votre projet CRM',
                'heroIntro' => 'OLING intervient côté métier pour cadrer les processus clients, le référentiel, les données, les interfaces, le choix de solution et la recette du CRM.',
                'promise' => 'OLING n’est ni éditeur, ni revendeur, ni intégrateur CRM par défaut. La mission consiste à traduire les besoins commerciaux et de service en processus, règles de gestion, données, critères de choix et scénarios de recette vérifiables.',
                'positioningTitle' => 'Quand engager une AMOA CRM',
                'scopeTitle' => 'Processus et référentiel client à cadrer',
                'triggerTitle' => 'Méthode de cadrage et de déploiement',
                'deliverablesTitle' => 'Livrables utiles au projet CRM',
                'projectContextsTitle' => 'Données, interfaces et responsabilités',
                'clientTypesTitle' => 'Recette et pilotage de l’adoption',
                'linksTitle' => 'Sujets directement liés au CRM',
                'linksIntro' => 'Le CRM doit rester relié au SI transverse, aux applications de gestion et aux obligations qui concernent les données personnelles.',
                'focus' => [
                    'Définir le référentiel client : personnes, entreprises, contacts, relations, source maître, règles de dédoublonnage et responsabilités de mise à jour.',
                    'Décrire les processus réellement couverts : prospect, opportunité, vente, demande, service ou réclamation selon le périmètre retenu.',
                    'Formaliser le pipeline commercial : étapes, critères de qualification, rôles, règles de passage et méthode de prévision.',
                    'Préciser les indicateurs attendus sur l’activité, le pipeline, le service, la qualité des données et l’adoption.',
                ],
                'missionPhases' => [
                    'Observer les usages réels, les fichiers parallèles, les données manquantes et les étapes du processus qui échappent au CRM.',
                    'Arbitrer le modèle cible, les rôles, les règles de gestion, le périmètre fonctionnel et la gouvernance du référentiel client.',
                    'Comparer les solutions sur des scénarios métier et des exigences mesurables, puis documenter les choix entre standard, paramétrage et spécifique.',
                    'Piloter la conception, la reprise, les interfaces, la recette, le déploiement et le suivi de l’adoption.',
                ],
                'deliverables' => [
                    'Note de cadrage, cartographie des processus et gouvernance du projet.',
                    'Modèle de données client, règles de gestion, expression des besoins et backlog métier.',
                    'Dossier de consultation, scénarios de démonstration, grille de choix et dossier d’arbitrage.',
                    'Plan de reprise, catalogue d’interfaces, stratégie de recette et plan de déploiement.',
                ],
                'projectContexts' => [
                    'La reprise doit préciser les sources, l’historique conservé, le nettoyage, le mapping, les doublons et le responsable métier de chaque validation.',
                    'Les interfaces sont décrites avec leur sens, leur fréquence, leurs données et leurs contrôles : ERP, facturation, marketing, portail, BI, messagerie ou applications métier.',
                    'Les traitements de données personnelles sont cadrés selon leur finalité, leur base légale, la minimisation, la conservation, les droits et la traçabilité attendue.',
                    'La source maître et les règles de synchronisation doivent être décidées avant les tests de migration et d’intégration.',
                ],
                'clientTypes' => [
                    'La recette couvre les processus métier, les profils et droits, les interfaces, la migration, les calculs du pipeline et le reporting.',
                    'Les cas de test incluent les variantes et exceptions, pas seulement le parcours commercial nominal.',
                    'L’adoption se suit par la complétude, la fraîcheur des données, l’usage des étapes du pipeline et la réduction des fichiers parallèles.',
                    'Les anomalies sont qualifiées par impact métier et reliées à des critères d’acceptation avant la mise en production.',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systèmes d’information', 'description' => 'Pour la gouvernance transverse et le pilotage global du projet.'],
                    ['href' => '/business-apps/erp', 'label' => 'AMOA ERP', 'description' => 'Pour les interfaces de gestion, la facturation et les référentiels partagés.'],
                    ['href' => '/expertises-audit/rgpd', 'label' => 'RGPD opérationnel', 'description' => 'Pour approfondir la gouvernance des traitements et les contrôles associés.'],
                ],
                'finalCtaTitle' => 'Qualifier le périmètre métier et les données du CRM',
                'finalCtaText' => 'L’échange initial porte sur les processus à couvrir, les sources de données, les interfaces, les décisions en attente et les critères de réussite du projet.',
                'schemaServiceType' => 'AMOA CRM',
            ],
            'gmao' => [
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'metaTitle' => 'AMOA GMAO : cadrage, choix et déploiement de votre GMAO | OLING',
                'metaDescription' => 'Cabinet AMOA GMAO indépendant : cadrage maintenance, choix de solution, données équipements, pilotage intégrateur, recette, migration et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA GMAO indépendant',
                'heroTitle' => 'AMOA GMAO : cadrer, choisir et réussir votre projet de maintenance',
                'heroIntro' => 'OLING accompagne les directions maintenance, exploitation, patrimoine et SI pour cadrer les processus, structurer les données équipements, choisir la bonne GMAO et piloter un déploiement utile sur le terrain.',
                'promise' => 'OLING n’est ni éditeur, ni revendeur, ni intégrateur GMAO. L’AMOA définit les processus de maintenance, les données du patrimoine, les interfaces et les scénarios de recette qui permettront de comparer puis de déployer une solution.',
                'positioningTitle' => 'Quand engager une AMOA GMAO',
                'scopeTitle' => 'Patrimoine et processus de maintenance à cadrer',
                'triggerTitle' => 'Méthode de cadrage et de consultation',
                'deliverablesTitle' => 'Livrables utiles au projet GMAO',
                'projectContextsTitle' => 'Données, mobilité, stocks et interfaces',
                'clientTypesTitle' => 'Recette et mise en service',
                'linksTitle' => 'Approfondir le cadrage GMAO',
                'linksIntro' => 'La page owner couvre le projet GMAO dans son ensemble ; la ressource eau et assainissement approfondit un contexte sectoriel précis.',
                'focus' => [
                    'Définir le patrimoine : sites, installations, équipements, localisation, criticité et responsabilités de mise à jour.',
                    'Construire une arborescence technique adaptée aux historiques, aux coûts, aux interventions, au reporting et à la traçabilité.',
                    'Décrire la maintenance préventive et corrective : gammes, demandes, ordres de travail, planification, exécution et clôture.',
                    'Préciser les indicateurs attendus sur les actifs, les délais, les coûts, les pannes et la réalisation du préventif.',
                ],
                'missionPhases' => [
                    'Observer les pratiques terrain, les écarts entre sites, les fichiers parallèles et la qualité des historiques disponibles.',
                    'Arbitrer l’arborescence, les référentiels, les rôles, les règles de gestion et le périmètre de reprise.',
                    'Consulter les solutions à partir d’exigences et de scénarios métier permettant de comparer les réponses sur des cas réels.',
                    'Piloter la conception, les interfaces, la reprise, la recette, le déploiement terrain et la stabilisation.',
                ],
                'deliverables' => [
                    'Diagnostic, cartographie des processus et modèle d’arborescence technique.',
                    'Expression des besoins, cahier des exigences et modèle de données équipements.',
                    'Scénarios de démonstration, grille de choix et dossier d’arbitrage.',
                    'Plan de reprise, catalogue d’interfaces, stratégie de recette et plan de déploiement.',
                ],
                'projectContexts' => [
                    'La mobilité doit préciser les usages hors connexion, la saisie terrain, les photos, les pièces jointes et les règles de clôture.',
                    'La gestion des stocks couvre les pièces, mouvements, seuils, inventaires et liens avec les achats ou la Finance selon le contexte.',
                    'Les interfaces peuvent concerner l’ERP, le SIG, la supervision, le SI client, la Finance, les RH ou la BI ; chacune doit avoir un propriétaire et des contrôles.',
                    'La reprise distingue les équipements, référentiels, historiques, gammes et données à nettoyer avant chargement.',
                ],
                'clientTypes' => [
                    'La recette ne vérifie pas seulement les écrans : elle exécute une demande, une planification, une intervention mobile, une consommation de pièce et une clôture complète.',
                    'Les scénarios couvrent le préventif, le correctif, les droits, les interfaces, la reprise et les cas d’erreur.',
                    'Les critères d’acceptation définissent les résultats attendus, les données de preuve et le traitement des anomalies.',
                    'La mise en service prévoit la préparation des référentiels, l’assistance terrain et le suivi des premiers cycles de maintenance.',
                ],
                'supportLinks' => [
                    ['href' => '/amoa-si', 'label' => 'AMOA des systèmes d’information', 'description' => 'Pour la gouvernance transverse et le pilotage global du projet.'],
                    ['href' => '/ressources/amoa-gmao-eau-et-assainissement-structurer-l-amont-pour-s-curiser-la-trajectoire', 'label' => 'Cadrage GMAO eau et assainissement', 'description' => 'Pour approfondir le patrimoine, le SIG, les référentiels et les usages terrain dans ce secteur.'],
                    ['href' => '/business-apps/erp', 'label' => 'AMOA ERP', 'description' => 'Pour les interfaces achats, stocks, Finance et référentiels partagés.'],
                ],
                'finalCtaTitle' => 'Qualifier le patrimoine, les processus et les données GMAO',
                'finalCtaText' => 'L’échange initial porte sur le périmètre des actifs, les pratiques terrain, les interfaces, la reprise et les scénarios qui devront être testés.',
                'schemaServiceType' => 'AMOA GMAO',
            ],
            'si-finance' => [
                'showHeroSideHtml' => false,
                'showReadingPath' => false,
                'showAutomaticZones' => false,
                'metaTitle' => 'AMOA SI Finance : cadrage et pilotage de transformation | OLING',
                'metaDescription' => 'Cabinet AMOA SI Finance indépendant : cadrage, processus Finance, ERP/EPM, reporting, données, choix de solution, pilotage, recette et conduite du changement.',
                'heroBadge' => 'Cabinet AMOA SI Finance indépendant',
                'heroTitle' => 'AMOA SI Finance : cadrer et piloter votre transformation Finance',
                'heroIntro' => 'OLING accompagne les directions Finance et DSI pour cadrer les processus, fiabiliser les données, clarifier les outils ERP ou EPM et piloter une transformation SI Finance utile, gouvernée et déployable.',
                'promise' => 'OLING n’est ni éditeur EPM, ni intégrateur financier, ni cabinet comptable. L’AMOA SI Finance traduit les besoins de la fonction Finance en processus, référentiels, contrôles, interfaces et scénarios de recette exploitables par la DSI et l’intégrateur.',
                'positioningTitle' => 'Quand engager une AMOA SI Finance',
                'scopeTitle' => 'Processus financiers et décisions fonctionnelles',
                'triggerTitle' => 'Méthode de cadrage et de transformation',
                'deliverablesTitle' => 'Livrables fonctionnels et de pilotage',
                'projectContextsTitle' => 'Référentiels, interfaces et migration',
                'clientTypesTitle' => 'Recette du système d’information financier',
                'linksTitle' => 'Sujets directement liés au SI Finance',
                'linksIntro' => 'Le SI Finance s’inscrit dans un ERP plus large et doit intégrer les flux de facturation électronique sans dupliquer leur cadrage détaillé.',
                'focus' => [
                    'Décrire la comptabilité générale, auxiliaire et analytique selon le périmètre, avec les contrôles et responsabilités associés.',
                    'Cadrer la préparation budgétaire, les engagements, le réalisé, les prévisions et les règles d’arbitrage.',
                    'Qualifier les axes analytiques, les rapprochements, le contrôle de gestion et les données nécessaires aux décisions.',
                    'Définir les sources du reporting, les règles de consolidation, les contrôles de clôture et la production des états.',
                ],
                'missionPhases' => [
                    'Observer les opérations réelles, les retraitements manuels, les délais de clôture et les ruptures entre comptabilité, budget, trésorerie et contrôle de gestion.',
                    'Arbitrer le modèle cible, les règles de gestion, les responsabilités, les contrôles et le périmètre fonctionnel des outils.',
                    'Comparer les scénarios ERP, EPM ou outils satellites sur des cas financiers représentatifs et des critères mesurables.',
                    'Piloter la conception, les interfaces, la migration, la recette, la bascule et la stabilisation avec la Finance et la DSI.',
                ],
                'deliverables' => [
                    'Diagnostic SI Finance, cartographie des processus et note de cadrage.',
                    'Expression des besoins, architecture fonctionnelle cible, règles de gestion et gouvernance projet.',
                    'Modèle de référentiels, catalogue d’interfaces et dossier d’arbitrage entre solutions.',
                    'Plan de migration, stratégie de recette, plan de bascule et indicateurs de stabilisation.',
                ],
                'projectContexts' => [
                    'Les référentiels à gouverner peuvent inclure les tiers, comptes, axes analytiques, structures, taxes et centres de coûts.',
                    'Les interfaces relient selon le contexte ERP, achats, ventes, paie, immobilisations, trésorerie, BI et applications métier.',
                    'La migration précise les historiques, soldes, tiers et référentiels repris ainsi que les contrôles de complétude et de rapprochement.',
                    'La facturation électronique est intégrée aux flux comptables et aux interfaces ; son cadrage réglementaire détaillé reste porté par la page RFE.',
                ],
                'clientTypes' => [
                    'La recette couvre les scénarios comptables, budgétaires et analytiques ainsi que les profils, droits et contrôles associés.',
                    'Chaque interface est testée avec ses données d’entrée, ses écritures attendues, ses rejets et ses rapprochements.',
                    'Les reprises sont validées sur les soldes, historiques, tiers et référentiels avant l’ouverture du système.',
                    'Le reporting et la clôture sont testés de bout en bout, depuis la source jusqu’aux états et contrôles de cohérence.',
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
                    ['href' => '/amoa-si', 'label' => 'AMOA des systèmes d’information', 'description' => 'Pour la gouvernance transverse et le pilotage global du projet.'],
                    ['href' => '/business-apps/erp', 'label' => 'AMOA ERP', 'description' => 'Pour le périmètre ERP global, les interfaces et le pilotage de l’intégrateur.'],
                    ['href' => '/consulting/reforme-facturation-electronique-amoa', 'label' => 'AMOA facturation électronique', 'description' => 'Pour les flux entrants et sortants, la plateforme agréée, les statuts et la recette RFE.'],
                ],
                'finalCtaTitle' => 'Qualifier les processus, référentiels et contrôles du SI Finance',
                'finalCtaText' => 'L’échange initial porte sur les opérations à couvrir, les données, les interfaces, les difficultés de clôture et les décisions fonctionnelles à prendre.',
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
