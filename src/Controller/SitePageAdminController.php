<?php

namespace App\Controller;

use App\Entity\SitePage;
use App\Form\SitePageType;
use App\Repository\SitePageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/pages')]
class SitePageAdminController extends AbstractController
{
    private const MANAGED_SITE_PAGES = [
        'apropos' => 'A propos',
        'metiers' => 'Secteurs d activite',
        'client' => 'References clients',
        'team' => 'Notre equipe',
        'amoa-si' => 'AMOA SI',
        'erp-progiciel' => 'AMOA ERP et progiciel',
        'crm' => 'AMOA CRM',
        'gmao' => 'AMOA GMAO',
        'rgpd' => 'Conseil RGPD',
        'cyber-securite' => 'Conseil cyber securite',
        'conseil-qualite' => 'Conseil qualite',
        'conformite-reglementaire' => 'Conformite reglementaire',
        'si-finance' => 'AMOA SI finance',
        'facturation-electronique-amoa' => 'Facturation electronique AMOA',
        'infrastructure-si-amoa' => 'Infrastructure SI AMOA',
        'public-pme-eti' => 'Conseil secteur public, PME et ETI',
        'mapsi-progiciel' => 'MAPSI progiciel',
        'hexagone-drom-dom-tom' => 'Conseil Hexagone et DROM',
        'gestion-risques-audit-controle-interne' => 'Gestion des risques, audit et controle interne',
        'direction-qualite-deleguee' => 'Direction qualite deleguee',
        'direction-conformite-externalisee' => 'Direction conformite externalisee',
        'dsi-externalisee' => 'DSI externalisee',
        'cabinet-conseil-paris' => 'Cabinet de conseil a Paris',
        'cabinet-conseil-lyon' => 'Cabinet de conseil a Lyon',
        'cabinet-conseil-toulouse' => 'Cabinet de conseil a Toulouse',
        'cabinet-conseil-montpellier' => 'Cabinet de conseil a Montpellier',
        'cabinet-conseil-nantes' => 'Cabinet de conseil a Nantes',
        'cabinet-conseil-bordeaux' => 'Cabinet de conseil a Bordeaux',
        'cabinet-conseil-guadeloupe' => 'Cabinet de conseil en Guadeloupe',
        'cabinet-conseil-martinique' => 'Cabinet de conseil en Martinique',
        'cabinet-conseil-reunion' => 'Cabinet de conseil a La Reunion',
        'cabinet-conseil-guyane' => 'Cabinet de conseil en Guyane',
        'cabinet-conseil-saint-pierre-et-miquelon' => 'Cabinet de conseil a Saint-Pierre-et-Miquelon',
        'metropoles-hexagone' => 'Conseil dans les metropoles de l Hexagone',
        'ressources' => 'Ressources et guides',
        'ressource-registre-traitements-rgpd' => 'Registre des traitements RGPD : methode complete',
        'ressource-aipd-rgpd-methode' => 'AIPD RGPD : quand la faire et comment la reussir',
        'ressource-feuille-route-cyber-pme-eti' => 'Feuille de route cyber securite PME/ETI : modele 12 mois',
        'ressource-nis2-dora-par-ou-commencer' => 'NIS2 et DORA : par ou commencer en 2026 ?',
        'ressource-indicateurs-qualite-si' => 'Indicateurs qualite SI : les KPI qui pilotent vraiment',
        'ressource-cadrage-projet-amoa-si' => 'Cadrage projet AMOA SI : 10 erreurs a eviter',
        'ressource-transformation-si-secteur-public' => 'Transformation SI dans le secteur public : plan d action',
        'ressource-choisir-cabinet-conseil-amoa-pme-eti' => 'Comment choisir un cabinet conseil AMOA SI pour PME/ETI ?',
    ];

    #[Route('', name: 'admin_pages_index', methods: ['GET'])]
    #[Route('/', name: 'admin_pages_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $repository, EntityManagerInterface $entityManager): Response
    {
        $this->ensureManagedPages($repository, $entityManager);

        return $this->render('admin/pages/index.html.twig', [
            'pages' => $repository->findBy([], ['slug' => 'ASC']),
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_pages_edit', methods: ['GET', 'POST'])]
    public function edit(SitePage $page, Request $request, EntityManagerInterface $entityManager): Response
    {
        $isSeoPage = $this->isSeoPageSlug($page->getSlug());
        $form = $this->createForm(SitePageType::class, $page, [
            'is_seo_page' => $isSeoPage,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Page mise à jour.');
            return $this->redirectToRoute('admin_pages_index');
        }

        return $this->render('admin/pages/edit.html.twig', [
            'form' => $form,
            'page' => $page,
            'isSeoPage' => $isSeoPage,
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

    private function isSeoPageSlug(?string $slug): bool
    {
        if ($slug === null) {
            return false;
        }

        return in_array($slug, [
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
        ], true);
    }
}
