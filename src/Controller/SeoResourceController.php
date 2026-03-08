<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Service\SimpleMarkdownParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoResourceController extends AbstractController
{
    #[Route('/ressources', name: 'seo_resources_index', options: ['sitemap' => true])]
    public function index(PracticeRepository $practiceRepository, ServicesRepository $servicesRepository): Response
    {
        return $this->render('seo/resources-index.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'articles' => array_values(self::articles()),
        ]);
    }

    #[Route('/ressources/{slug}', name: 'seo_resource', options: ['sitemap' => false])]
    public function show(
        string $slug,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        SimpleMarkdownParser $markdownParser
    ): Response {
        $articles = self::articles();

        if (!isset($articles[$slug])) {
            throw $this->createNotFoundException('La ressource demandee n\'existe pas.');
        }

        $article = $articles[$slug];
        $filePath = $this->getParameter('kernel.project_dir') . '/content/seo/' . $article['file'];

        if (!is_file($filePath)) {
            throw $this->createNotFoundException('Le contenu de la ressource est indisponible.');
        }

        $markdown = (string) file_get_contents($filePath);

        return $this->render('seo/resource-article.html.twig', [
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'pract' => '',
            'article' => $article,
            'content_html' => $markdownParser->toHtml($markdown),
            'related' => array_values(array_filter($articles, static fn (array $item): bool => $item['slug'] !== $slug)),
        ]);
    }

    /**
     * @return array<string, array{slug: string, file: string, title: string, meta: string, h1: string, intro: string}>
     */
    public static function articles(): array
    {
        return [
            'registre-traitements-rgpd' => [
                'slug' => 'registre-traitements-rgpd',
                'file' => 'registre-traitements-rgpd.md',
                'title' => 'Registre des traitements RGPD : methode complete | OLING',
                'meta' => 'Construisez un registre des traitements RGPD fiable : methode, exemples, gouvernance et erreurs a eviter pour secteur public, PME et ETI.',
                'h1' => 'Registre des traitements RGPD : methode operationnelle pour public, PME et ETI',
                'intro' => 'Guide pratique pour construire un registre exploitable, pilotable et defensable en audit.',
            ],
            'aipd-rgpd-methode' => [
                'slug' => 'aipd-rgpd-methode',
                'file' => 'aipd-rgpd-methode.md',
                'title' => 'AIPD RGPD : quand la faire et comment la reussir | OLING',
                'meta' => 'AIPD RGPD : declencheurs, methode, livrables et gouvernance. Guide pratique pour organisations publiques, PME et ETI.',
                'h1' => 'AIPD RGPD : quand la lancer, comment la conduire, quoi livrer',
                'intro' => 'Une methode claire pour passer de l\'obligation reglementaire a un pilotage concret des risques.',
            ],
            'feuille-route-cyber-pme-eti' => [
                'slug' => 'feuille-route-cyber-pme-eti',
                'file' => 'feuille-route-cyber-pme-eti.md',
                'title' => 'Feuille de route cyber securite PME/ETI : modele 12 mois | OLING',
                'meta' => 'Construisez une feuille de route cyber securite realiste pour PME et ETI : priorites, budget, gouvernance SSI, indicateurs et quick wins.',
                'h1' => 'Feuille de route cyber securite PME et ETI : modele concret sur 12 mois',
                'intro' => 'Un plan pragmatique pour reduire les risques sans bloquer l\'execution metier.',
            ],
            'nis2-dora-par-ou-commencer' => [
                'slug' => 'nis2-dora-par-ou-commencer',
                'file' => 'nis2-dora-par-ou-commencer.md',
                'title' => 'NIS2 et DORA : par ou commencer en 2026 ? | OLING',
                'meta' => 'NIS2 et DORA : priorites de conformite, gouvernance, preuves attendues et plan de mise en oeuvre pour secteur public, PME et ETI.',
                'h1' => 'NIS2 et DORA : plan de demarrage pragmatique pour vos equipes',
                'intro' => 'Comment structurer un demarrage rapide et eviter les projets de conformite purement documentaires.',
            ],
            'indicateurs-qualite-si' => [
                'slug' => 'indicateurs-qualite-si',
                'file' => 'indicateurs-qualite-si.md',
                'title' => 'Indicateurs qualite SI : les KPI qui pilotent vraiment | OLING',
                'meta' => 'Selectionnez les bons indicateurs qualite SI pour piloter la performance, la conformite et la satisfaction utilisateurs dans le public, PME et ETI.',
                'h1' => 'Indicateurs qualite SI : les KPI essentiels pour piloter la performance',
                'intro' => 'Les tableaux de bord qui aident vraiment les decideurs a arbitrer et a agir.',
            ],
            'cadrage-projet-amoa-si' => [
                'slug' => 'cadrage-projet-amoa-si',
                'file' => 'cadrage-projet-amoa-si.md',
                'title' => 'Cadrage projet AMOA SI : 10 erreurs a eviter | OLING',
                'meta' => 'Evitez les erreurs de cadrage AMOA SI qui coutent cher : perimetre, gouvernance, risques, planning, qualite et criteres de succes.',
                'h1' => 'Cadrage projet AMOA SI : 10 erreurs qui font derailler vos programmes',
                'intro' => 'Un referentiel operationnel pour securiser la phase de lancement et l\'execution.',
            ],
            'transformation-si-secteur-public' => [
                'slug' => 'transformation-si-secteur-public',
                'file' => 'transformation-si-secteur-public.md',
                'title' => 'Transformation SI dans le secteur public : plan d\'action | OLING',
                'meta' => 'Methode de transformation SI pour le secteur public : gouvernance, priorisation, conformite RGPD/cyber, conduite du changement et resultats.',
                'h1' => 'Transformation SI secteur public : gouvernance, priorites et execution',
                'intro' => 'Une trajectoire concrete pour moderniser sans perdre la continuite de service.',
            ],
            'choisir-cabinet-conseil-amoa-pme-eti' => [
                'slug' => 'choisir-cabinet-conseil-amoa-pme-eti',
                'file' => 'choisir-cabinet-conseil-amoa-pme-eti.md',
                'title' => 'Comment choisir un cabinet conseil AMOA SI pour PME/ETI ? | OLING',
                'meta' => 'Criteres concrets pour choisir un cabinet de conseil AMOA SI en PME/ETI : expertise, methode, livrables, gouvernance, ROI et engagement.',
                'h1' => 'Choisir un cabinet de conseil AMOA SI pour PME et ETI : la grille decisive',
                'intro' => 'La methode de selection pour securiser vos budgets, vos delais et vos resultats.',
            ],
        ];
    }
}
