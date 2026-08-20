<?php

namespace App\Controller;

use App\Entity\HomeSection;
use App\Entity\SitePage;
use App\Form\HomeHeroType;
use App\Form\HomeAwardsSectionType;
use App\Form\HomeSectionType;
use App\Repository\MetierRepository;
use App\Repository\SitePageRepository;
use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\HomeSectionRepository;
use App\Repository\HomeAwardItemRepository;
use App\Service\PublicSiteConfig;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/home')]
class HomeSectionAdminController extends AbstractController
{
    #[Route('/hero', name: 'admin_home_hero', methods: ['GET', 'POST'])]
    public function hero(
        SitePageRepository $sitePageRepository,
        MetierRepository $metierRepository,
        EntityManagerInterface $entityManager,
        PublicSiteConfig $publicSiteConfig,
        Request $request
    ): Response
    {
        $page = $this->findOrCreateHomePage($sitePageRepository, $entityManager, $publicSiteConfig);
        $payload = $this->decodeStructuredPayload($page->getBodyHtml());
        $heroConfig = $payload['hero'] ?? [];
        $selectedMetier = null;
        if (is_string($heroConfig['metierSlug'] ?? null) && trim($heroConfig['metierSlug']) !== '') {
            $selectedMetier = $metierRepository->findOneBy(['slug' => trim($heroConfig['metierSlug'])]);
        }

        $form = $this->createForm(HomeHeroType::class, $page, [
            'cta_defaults' => [
                'ctaLabelSecondary' => $heroConfig['secondaryCta']['label'] ?? null,
                'ctaUrlSecondary' => $this->resolveCtaAdminValue($heroConfig['secondaryCta'] ?? null),
            ],
            'tags_default' => implode("\n", $this->normalizeStringList($heroConfig['tags'] ?? null)),
            'selected_metier' => $selectedMetier,
            'metier_intro_default' => is_string($heroConfig['metierIntro'] ?? null) ? $heroConfig['metierIntro'] : null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selectedMetier = $form->get('metier')->getData();
            $payload['hero'] = is_array($payload['hero'] ?? null) ? $payload['hero'] : [];
            $payload['hero']['eyebrow'] = trim(strip_tags((string) $form->get('eyebrow')->getData()));
            $payload['hero']['titleLines'] = $this->normalizeTitleLines((string) $form->get('title')->getData());
            $payload['hero']['intro'] = trim(strip_tags((string) $form->get('intro')->getData()));
            $payload['hero']['tags'] = $this->normalizeLines((string) $form->get('tagsText')->getData());
            unset($payload['hero']['primaryCta']);
            $payload['hero']['secondaryCta'] = $this->normalizeCtaPayload(
                (string) $form->get('ctaLabelSecondary')->getData(),
                (string) $form->get('ctaUrlSecondary')->getData(),
                $payload['hero']['secondaryCta'] ?? null
            );
            $payload['hero']['metierSlug'] = $selectedMetier?->getSlug();
            $payload['hero']['metierIntro'] = $this->normalizeNullableText((string) $form->get('metierIntro')->getData());

            if ($selectedMetier !== null) {
                $payload['hero']['portraitImage'] = $selectedMetier->getImageHero() ?: $selectedMetier->getImage();
                $payload['hero']['portraitAlt'] = $selectedMetier->getDesignation();
            }

            $page->setHeroImage(null);
            $page->setBodyHtml($this->encodeStructuredPayload($payload));

            $entityManager->flush();
            $this->addFlash('success', 'Hero mis à jour.');

            return $this->redirectToRoute('admin_home_hero');
        }

        return $this->render('admin/home/hero.html.twig', [
            'form' => $form,
            'page' => $page,
            'practices' => [],
        ]);
    }

    #[Route('/content', name: 'admin_home_content', methods: ['GET', 'POST'])]
    public function content(
        SitePageRepository $sitePageRepository,
        EntityManagerInterface $entityManager,
        PublicSiteConfig $publicSiteConfig,
        UploadManager $uploadManager,
        Request $request
    ): Response {
        $page = $this->findOrCreateHomePage($sitePageRepository, $entityManager, $publicSiteConfig);
        $form = $this->createForm(\App\Form\SitePageType::class, $page, [
            'editor_mode' => 'home',
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

                    return $this->redirectToRoute('admin_home_content');
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Contenu de la home mis à jour.');

            return $this->redirectToRoute('admin_home_content');
        }

        return $this->render('admin/pages/edit.html.twig', [
            'form' => $form,
            'page' => $page,
            'editorMode' => 'home',
            'practices' => [],
        ]);
    }

    #[Route('/practices', name: 'admin_home_practices', methods: ['GET', 'POST'])]
    public function practices(HomeSectionRepository $repository, PracticeRepository $practiceRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'practices']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('practices');
            $entityManager->persist($section);
        }

        $practiceItems = $practiceRepository->findBy([], ['designation' => 'ASC']);
        $featuredItems = array_values(array_filter($practiceItems, static fn ($practice) => $practice->isFeaturedHome()));
        usort($featuredItems, static function ($a, $b) {
            $rankA = $a->getFeaturedHomeRank() ?? 9999;
            $rankB = $b->getFeaturedHomeRank() ?? 9999;

            if ($rankA === $rankB) {
                return strcasecmp((string) $a->getDesignation(), (string) $b->getDesignation());
            }

            return $rankA <=> $rankB;
        });

        $form = $this->createForm(HomeSectionType::class, $section);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $request->request->get('featured_form') === 'practices') {
            if (!$this->isCsrfTokenValid('home_practices', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Jeton invalide.');

                return $this->redirectToRoute('admin_home_practices');
            }

            $selectedIds = array_values(array_unique(array_map('intval', $request->request->all('featured_ids'))));
            if (count($selectedIds) > 4) {
                $this->addFlash('warning', 'Maximum 4 practices sur la home.');

                return $this->redirectToRoute('admin_home_practices');
            }

            $position = 1;
            foreach ($practiceItems as $practice) {
                if (in_array($practice->getId(), $selectedIds, true)) {
                    $practice->setFeaturedHome(true);
                    $practice->setFeaturedHomeRank($position);
                    ++$position;
                    continue;
                }

                $practice->setFeaturedHome(false);
                $practice->setFeaturedHomeRank(null);
            }

            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Sélection des practices mise à jour.');

            return $this->redirectToRoute('admin_home_practices');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_practices');
        }

        return $this->render('admin/home/practices.html.twig', [
            'form' => $form,
            'section' => $section,
            'practiceItems' => $practiceItems,
            'featuredItems' => $featuredItems,
            'practices' => [],
        ]);
    }

    #[Route('/projects', name: 'admin_home_projects', methods: ['GET', 'POST'])]
    public function projects(HomeSectionRepository $repository, ProjetRepository $projetRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'projects']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('projects');
            $entityManager->persist($section);
        }

        $projectItems = $projetRepository->findBy([], ['designation' => 'ASC']);
        $featuredItems = array_values(array_filter($projectItems, static fn ($projet) => $projet->isFeaturedProjects()));
        usort($featuredItems, static function ($a, $b) {
            $rankA = $a->getFeaturedProjectsRank() ?? 9999;
            $rankB = $b->getFeaturedProjectsRank() ?? 9999;

            if ($rankA === $rankB) {
                return strcasecmp((string) $a->getDesignation(), (string) $b->getDesignation());
            }

            return $rankA <=> $rankB;
        });

        $form = $this->createForm(HomeSectionType::class, $section);
        $form->handleRequest($request);

        if ($request->isMethod('POST') && $request->request->get('featured_form') === 'projects') {
            if (!$this->isCsrfTokenValid('home_projets', (string) $request->request->get('_token'))) {
                $this->addFlash('danger', 'Jeton invalide.');

                return $this->redirectToRoute('admin_home_projects');
            }

            $selectedIds = array_values(array_unique(array_map('intval', $request->request->all('featured_ids'))));
            if (count($selectedIds) > 6) {
                $this->addFlash('warning', 'Maximum 6 projets sur la home.');

                return $this->redirectToRoute('admin_home_projects');
            }

            $position = 1;
            foreach ($projectItems as $projet) {
                if (in_array($projet->getId(), $selectedIds, true)) {
                    $projet->setFeaturedProjects(true);
                    $projet->setFeaturedProjectsRank($position);
                    ++$position;
                    continue;
                }

                $projet->setFeaturedProjects(false);
                $projet->setFeaturedProjectsRank(null);
            }

            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Sélection des projets mise à jour.');

            return $this->redirectToRoute('admin_home_projects');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_projects');
        }

        return $this->render('admin/home/projects.html.twig', [
            'form' => $form,
            'section' => $section,
            'projets' => $projectItems,
            'featuredItems' => $featuredItems,
            'practices' => [],
        ]);
    }

    #[Route('/awards', name: 'admin_home_awards', methods: ['GET', 'POST'])]
    public function awards(HomeSectionRepository $repository, HomeAwardItemRepository $awardRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'awards']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('awards');
            $entityManager->persist($section);
        }

        $form = $this->createForm(HomeAwardsSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_awards');
        }

        return $this->render('admin/home/awards.html.twig', [
            'form' => $form,
            'section' => $section,
            'awards' => $awardRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'practices' => [],
        ]);
    }

    private function findOrCreateHomePage(SitePageRepository $sitePageRepository, EntityManagerInterface $entityManager, PublicSiteConfig $publicSiteConfig): SitePage
    {
        $page = $sitePageRepository->findOneBy(['slug' => 'home']);
        if ($page instanceof SitePage) {
            return $page;
        }

        $defaults = $publicSiteConfig->getHome();

        $page = new SitePage();
        $page->setSlug('home');
        $page->setTitle((string) ($defaults['seoTitle'] ?? 'Accueil'));
        $page->setMetaDescription($defaults['metaDescription'] ?? null);
        $page->setHeroBadge($defaults['hero']['eyebrow'] ?? null);
        $page->setHeroTitle(isset($defaults['hero']['titleLines']) && is_array($defaults['hero']['titleLines']) ? implode('|', $defaults['hero']['titleLines']) : null);
        $page->setHeroIntro($defaults['hero']['intro'] ?? null);
        $page->setHeroImage($defaults['hero']['portraitImage'] ?? null);
        $page->setBodyHtml($this->encodeStructuredPayload($defaults));

        $entityManager->persist($page);
        $entityManager->flush();

        return $page;
    }

    private function decodeStructuredPayload(?string $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function encodeStructuredPayload(array $payload): string
    {
        return (string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }

    private function resolveCtaAdminValue(?array $cta): ?string
    {
        if (!is_array($cta)) {
            return null;
        }

        if (is_string($cta['url'] ?? null) && trim($cta['url']) !== '') {
            return $cta['url'];
        }

        $route = $cta['route'] ?? null;
        if (!is_string($route) || trim($route) === '') {
            return null;
        }

        return $this->generateUrl($route, is_array($cta['routeParams'] ?? null) ? $cta['routeParams'] : []);
    }

    private function normalizeCtaPayload(string $label, string $url, mixed $existing): ?array
    {
        $label = trim($label);
        $url = trim($url);
        $existing = is_array($existing) ? $existing : [];

        if ($label === '' && $url === '') {
            return $existing !== [] ? $existing : null;
        }

        $payload = [];
        if ($label !== '') {
            $payload['label'] = $label;
        } elseif (is_string($existing['label'] ?? null) && trim($existing['label']) !== '') {
            $payload['label'] = trim($existing['label']);
        }

        if ($url !== '') {
            $payload['url'] = $url;
        } else {
            foreach (['url', 'route', 'routeParams'] as $field) {
                if (array_key_exists($field, $existing)) {
                    $payload[$field] = $existing[$field];
                }
            }
        }

        return $payload !== [] ? $payload : null;
    }

    /**
     * @param mixed $values
     * @return array<int, string>
     */
    private function normalizeStringList(mixed $values): array
    {
        if (!is_array($values)) {
            return [];
        }

        return array_values(array_filter(array_map(
            static fn ($item): string => is_scalar($item) ? trim(strip_tags((string) $item)) : '',
            $values
        )));
    }

    /**
     * @return array<int, string>
     */
    private function normalizeLines(string $value): array
    {
        $parts = preg_split('/[\r\n,]+/', $value) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim(strip_tags($item)),
            $parts
        )));
    }

    /**
     * @return array<int, string>
     */
    private function normalizeTitleLines(string $value): array
    {
        $parts = preg_split('/[|]+/', $value) ?: [];

        return array_values(array_filter(array_map(
            static fn (string $item): string => trim(strip_tags($item)),
            $parts
        )));
    }

    private function normalizeNullableText(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }
}
