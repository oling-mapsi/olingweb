<?php

namespace App\Controller;

use App\Entity\SitePage;
use App\Form\SitePageType;
use App\Repository\SitePageRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/pages')]
class SitePageAdminController extends AbstractController
{
    private const MANAGED_SITE_PAGES = [
        'apropos' => 'À propos',
        'contact' => 'Contact',
        'services' => 'Services et offres',
        'projets' => 'Projets',
        'metiers' => 'Secteurs d’activité',
        'client' => 'Références clients',
        'team' => 'Notre équipe',
        'amoa-si' => 'AMOA SI',
        'erp-progiciel' => 'AMOA ERP et progiciel',
        'crm' => 'AMOA CRM',
        'gmao' => 'AMOA GMAO',
        'rgpd' => 'Conseil RGPD',
        'cyber-securite' => 'Conseil cybersécurité',
        'conseil-qualite' => 'Conseil qualité',
        'conformite-reglementaire' => 'Conformité réglementaire',
        'si-finance' => 'AMOA SI finance',
        'facturation-electronique-amoa' => 'Facturation électronique AMOA',
        'infrastructure-si-amoa' => 'Infrastructure SI AMOA',
        'public-pme-eti' => 'Conseil secteur public, PME et ETI',
        'mapsi-progiciel' => 'MAPSI progiciel',
        'hexagone-drom-dom-tom' => 'Conseil Hexagone et DROM',
        'gestion-risques-audit-controle-interne' => 'Gestion des risques, audit et contrôle interne',
        'direction-qualite-deleguee' => 'Direction qualité déléguée',
        'direction-conformite-externalisee' => 'Direction conformité externalisée',
        'dsi-externalisee' => 'DSI externalisée',
        'cabinet-conseil-paris' => 'Cabinet de conseil à Paris',
        'cabinet-conseil-lyon' => 'Cabinet de conseil à Lyon',
        'cabinet-conseil-toulouse' => 'Cabinet de conseil à Toulouse',
        'cabinet-conseil-montpellier' => 'Cabinet de conseil à Montpellier',
        'cabinet-conseil-nantes' => 'Cabinet de conseil à Nantes',
        'cabinet-conseil-bordeaux' => 'Cabinet de conseil à Bordeaux',
        'cabinet-conseil-guadeloupe' => 'Cabinet de conseil en Guadeloupe',
        'cabinet-conseil-martinique' => 'Cabinet de conseil en Martinique',
        'cabinet-conseil-reunion' => 'Cabinet de conseil à La Réunion',
        'cabinet-conseil-guyane' => 'Cabinet de conseil en Guyane',
        'cabinet-conseil-saint-pierre-et-miquelon' => 'Cabinet de conseil à Saint-Pierre-et-Miquelon',
        'metropoles-hexagone' => 'Conseil dans les métropoles de l’Hexagone',
        'ressources' => 'Ressources et guides',
        'expertises-index' => 'Hub expertises',
        'expertise-transformation-si-pme-eti' => 'Expertise transformation SI',
        'expertise-amoa-erp-applications-metiers' => 'Expertise AMOA ERP et applications métiers',
        'expertise-organisation-processus-conduite-du-changement' => 'Expertise organisation et changement',
        'expertise-data-automatisation-intelligence-artificielle' => 'Expertise data, automatisation et IA',
        'expertise-cybersecurite-conformite-resilience' => 'Expertise cybersécurité, conformité et résilience',
        'expertise-rgpd-dpo-gouvernance' => 'Expertise RGPD, DPO et gouvernance',
        'expertise-amoa-ia-pilotage-projets-agents' => 'Expertise AMOA IA, pilotage et agents',
        'expertise-conformite-ia-gouvernance-ai-act' => 'Expertise conformité IA et gouvernance',
        'expertise-transformation-digitale-ia-pme-pmi' => 'Expertise transformation digitale et IA',
        'secteurs-index' => 'Hub secteurs',
        'secteur-industrie' => 'Secteur industrie et PMI',
        'secteur-services' => 'Secteur services B2B',
        'secteur-secteur-public' => 'Secteur organisations régulées',
        'ressource-registre-traitements-rgpd' => 'Registre des traitements RGPD : méthode complète',
        'ressource-aipd-rgpd-methode' => 'AIPD RGPD : quand la faire et comment la réussir',
        'ressource-feuille-route-cyber-pme-eti' => 'Feuille de route cybersécurité PME/ETI : modèle 12 mois',
        'ressource-nis2-dora-par-ou-commencer' => 'NIS2 et DORA : par où commencer en 2026 ?',
        'ressource-indicateurs-qualite-si' => 'Indicateurs qualité SI : les KPI qui pilotent vraiment',
        'ressource-cadrage-projet-amoa-si' => 'Cadrage projet AMOA SI : 10 erreurs à éviter',
        'ressource-transformation-si-secteur-public' => 'Transformation SI dans le secteur public : plan d’action',
        'ressource-choisir-cabinet-conseil-amoa-pme-eti' => 'Comment choisir un cabinet conseil AMOA SI pour PME/ETI ?',
    ];

    #[Route('', name: 'admin_pages_index', methods: ['GET'])]
    #[Route('/', name: 'admin_pages_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $this->ensureManagedPages($repository, $entityManager);

        $pages = array_values(array_filter(
            $repository->findBy([], ['slug' => 'ASC']),
            static fn (SitePage $page): bool => $page->getSlug() !== 'home'
        ));

        return $this->render('admin/pages/index.html.twig', [
            'pages' => $pages,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_pages_edit', methods: ['GET', 'POST'])]
    public function edit(SitePage $page, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $editorMode = $this->getEditorMode($page->getSlug());
        $form = $this->createForm(SitePageType::class, $page, [
            'editor_mode' => $editorMode,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $heroImageFile = $form->get('heroImageFile')->getData();
            if ($heroImageFile) {
                try {
                    $oldPath = $page->getHeroImage();
                    $page->setHeroImage($uploadManager->upload($heroImageFile, 'pages/hero'));
                    $uploadManager->remove($oldPath);
                } catch (FileException $exception) {
                    $this->addFlash('danger', 'Impossible d\'envoyer l\'image hero.');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Page mise à jour.');
            return $this->redirectToRoute('admin_pages_index');
        }

        return $this->render('admin/pages/edit.html.twig', [
            'form' => $form,
            'page' => $page,
            'editorMode' => $editorMode,
            'practices' => [],
        ]);
    }

    private function ensureManagedPages(SitePageRepository $repository, EntityManagerInterface $entityManager): void
    {
        $knownSlugs = array_keys(self::MANAGED_SITE_PAGES);
        $existingPages = $repository->findBy(['slug' => $knownSlugs]);
        $existingBySlug = [];
        foreach ($existingPages as $page) {
            $existingBySlug[$page->getSlug()] = true;
        }

        $needsFlush = false;
        foreach (self::MANAGED_SITE_PAGES as $slug => $title) {
            if (isset($existingBySlug[$slug])) {
                continue;
            }

            $page = new SitePage();
            $page->setSlug($slug);
            $page->setTitle($title);
            $entityManager->persist($page);
            $needsFlush = true;
        }

        if ($needsFlush) {
            $entityManager->flush();
        }
    }

    private function getEditorMode(?string $slug): string
    {
        if ($slug === null) {
            return 'default';
        }

        if (in_array($slug, [
            'apropos',
            'contact',
            'client',
            'services',
            'metiers',
            'team',
            'projets',
        ], true)) {
            return 'editorial';
        }

        if (in_array($slug, [
            'amoa-si',
            'erp-progiciel',
            'crm',
            'gmao',
            'rgpd',
            'cyber-securite',
            'conseil-qualite',
            'conformite-reglementaire',
            'si-finance',
            'facturation-electronique-amoa',
            'infrastructure-si-amoa',
            'public-pme-eti',
            'mapsi-progiciel',
            'hexagone-drom-dom-tom',
            'gestion-risques-audit-controle-interne',
            'direction-qualite-deleguee',
            'direction-conformite-externalisee',
            'dsi-externalisee',
            'cabinet-conseil-paris',
            'cabinet-conseil-lyon',
            'cabinet-conseil-toulouse',
            'cabinet-conseil-montpellier',
            'cabinet-conseil-nantes',
            'cabinet-conseil-bordeaux',
            'cabinet-conseil-guadeloupe',
            'cabinet-conseil-martinique',
            'cabinet-conseil-reunion',
            'cabinet-conseil-guyane',
            'cabinet-conseil-saint-pierre-et-miquelon',
            'metropoles-hexagone',
            'ressources',
            'ressource-registre-traitements-rgpd',
            'ressource-aipd-rgpd-methode',
            'ressource-feuille-route-cyber-pme-eti',
            'ressource-nis2-dora-par-ou-commencer',
            'ressource-indicateurs-qualite-si',
            'ressource-cadrage-projet-amoa-si',
            'ressource-transformation-si-secteur-public',
            'ressource-choisir-cabinet-conseil-amoa-pme-eti',
        ], true)) {
            return 'seo';
        }

        if (in_array($slug, [
            'expertises-index',
            'expertise-transformation-si-pme-eti',
            'expertise-amoa-erp-applications-metiers',
            'expertise-organisation-processus-conduite-du-changement',
            'expertise-data-automatisation-intelligence-artificielle',
            'expertise-cybersecurite-conformite-resilience',
            'expertise-rgpd-dpo-gouvernance',
            'expertise-amoa-ia-pilotage-projets-agents',
            'expertise-conformite-ia-gouvernance-ai-act',
            'expertise-transformation-digitale-ia-pme-pmi',
            'secteurs-index',
            'secteur-industrie',
            'secteur-services',
            'secteur-secteur-public',
        ], true)) {
            return 'structured';
        }

        return 'default';
    }
}
