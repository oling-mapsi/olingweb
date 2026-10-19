<?php

namespace App\Controller;

use App\Entity\HomeSection;
use App\Entity\SitePage;
use App\Dto\StructuredContentAdminFormData;
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
use App\Service\StructuredContentTranslationSynchronizer;
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
    #[Route('', name: 'admin_home_index', methods: ['GET'])]
    #[Route('/', name: 'admin_home_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $repository): Response
    {
        $page = $repository->findOneBy(['slug' => 'home']);
        if (!$page) { throw $this->createNotFoundException('Accueil introuvable.'); }
        return $this->render('admin/home/index.html.twig', ['page' => $page, 'practices' => []]);
    }

    #[Route('/method', name: 'admin_home_method', methods: ['GET', 'POST'])]
    public function method(SitePageRepository $repository, EntityManagerInterface $entityManager, Request $request, UploadManager $uploadManager): Response
    {
        $page = $repository->findOneBy(['slug' => 'home']);
        if (!$page) { throw $this->createNotFoundException('Accueil introuvable.'); }
        $this->loadHomeEditorialState($page);
        $payload = $this->decodeStructuredPayload($page->getBodyHtml());
        $method = $payload['proof'] ?? [];
        $data = ['eyebrow' => $method['eyebrow'] ?? '', 'title' => $method['title'] ?? '', 'image' => $method['image'] ?? '', 'imageAlt' => $method['imageAlt'] ?? '', 'imageFit' => $method['imageFit'] ?? 'contain', 'imageSize' => $method['imageSize'] ?? 'full', 'imagePosition' => $method['imagePosition'] ?? 'center', 'items' => []];
        foreach ($method['items'] ?? [] as $key => $item) {
            $data['items'][$key] = ['title' => $item['title'] ?? '', 'text' => $item['text'] ?? ''];
        }
        $sourceHash = $this->homeEditorialHash($page);
        $builder = $this->createFormBuilder($data)
            ->add('eyebrow', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Surtitre', 'required' => false])
            ->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Titre de la section'])
            ->add('image', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Adresse de l’illustration', 'required' => false])
            ->add('imageAlt', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Texte alternatif de l’illustration', 'required' => false])
            ->add('imageFit', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['label' => 'Disposition de l’image', 'choices' => ['Image entière — sans recadrage' => 'contain', 'Remplir le cadre — image recadrée' => 'cover'], 'help' => 'Image entière conserve les proportions et affiche toute l’illustration.'])
            ->add('imageSize', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['label' => 'Taille dans la colonne', 'choices' => ['Toute la largeur' => 'full', 'Moyenne (80 %)' => 'medium', 'Petite (60 %)' => 'small']])
            ->add('imagePosition', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, ['label' => 'Zone à privilégier si l’image est recadrée', 'choices' => ['Centre' => 'center', 'Gauche' => 'left', 'Droite' => 'right', 'Haut' => 'top', 'Bas' => 'bottom']])
            ->add('imageFile', \Symfony\Component\Form\Extension\Core\Type\FileType::class, ['label' => 'Remplacer l’illustration', 'mapped' => false, 'required' => false, 'constraints' => [new \Symfony\Component\Validator\Constraints\File(['maxSize' => '5M', 'mimeTypes' => ['image/png','image/jpeg','image/webp','image/avif']])]])
            ->add('homeEditorSourceHash', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, ['mapped' => false, 'data' => $sourceHash]);
        $items = $builder->create('items', \Symfony\Component\Form\Extension\Core\Type\FormType::class, ['label' => false]);
        foreach ($data['items'] as $key => $item) {
            $entry = $builder->create((string) $key, \Symfony\Component\Form\Extension\Core\Type\FormType::class, ['label' => false])
                ->add('title', \Symfony\Component\Form\Extension\Core\Type\TextType::class, ['label' => 'Titre'])
                ->add('text', \Symfony\Component\Form\Extension\Core\Type\TextareaType::class, ['label' => 'Description', 'attr' => ['rows' => 3]]);
            $items->add($entry);
        }
        $builder->add($items);
        $form = $builder->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && !hash_equals($sourceHash, (string) $form->get('homeEditorSourceHash')->getData())) {
            $form->addError(new \Symfony\Component\Form\FormError('L’accueil a changé. Rechargez le formulaire avant de réessayer.'));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $submitted = $form->getData();
            $file = $form->get('imageFile')->getData();
            if ($file) {
                try { $submitted['image'] = $uploadManager->upload($file, 'pages/home'); }
                catch (FileException) { $form->addError(new \Symfony\Component\Form\FormError('Impossible d’envoyer l’illustration. Aucune modification n’a été enregistrée.')); }
            }
            if ($form->isValid()) {
                $payload['proof'] = $this->mergeHomeMethod($method, $submitted);
                $page->setBodyHtml($this->encodeStructuredPayload($payload));
                $this->saveHomeEditorialState($page);
                $entityManager->flush();
                $this->addFlash('success', 'Méthode d’intervention mise à jour.');
                return $this->redirectToRoute('admin_home_method');
            }
        }
        return $this->render('admin/home/method.html.twig', ['form' => $form->createView(), 'methodImage' => $method['image'] ?? null, 'practices' => []]);
    }

    private function mergeHomeMethod(array $existing, array $submitted): array
    {
        foreach (['eyebrow', 'title', 'image', 'imageAlt'] as $field) { $existing[$field] = trim((string) ($submitted[$field] ?? '')); }
        foreach (['imageFit' => ['contain', 'cover'], 'imageSize' => ['full', 'medium', 'small'], 'imagePosition' => ['center', 'left', 'right', 'top', 'bottom']] as $field => $allowed) {
            $value = $submitted[$field] ?? $existing[$field] ?? $allowed[0];
            $existing[$field] = in_array($value, $allowed, true) ? $value : $allowed[0];
        }
        foreach (array_keys($existing['items'] ?? []) as $key) {
            $existing['items'][$key]['title'] = trim((string) $submitted['items'][$key]['title']);
            $existing['items'][$key]['text'] = trim((string) $submitted['items'][$key]['text']);
        }
        return $existing;
    }

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
        $this->loadHomeEditorialState($page);
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
        $sourceHash = $this->homeEditorialHash($page);
        $form->add('homeEditorSourceHash', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, ['mapped' => false, 'data' => $sourceHash]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !hash_equals($sourceHash, (string) $form->get('homeEditorSourceHash')->getData())) {
            $form->addError(new \Symfony\Component\Form\FormError('L’accueil a changé depuis l’ouverture du formulaire. Rechargez-le avant de réessayer.'));
        }


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

            $this->saveHomeEditorialState($page);
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
        $this->loadHomeEditorialState($page);
        $form = $this->createForm(\App\Form\SitePageType::class, $page, [
            'editor_mode' => 'home',
        ]);
        $sourceHash = $this->homeEditorialHash($page);
        $form->add('homeEditorSourceHash', \Symfony\Component\Form\Extension\Core\Type\HiddenType::class, ['mapped' => false, 'data' => $sourceHash]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && !hash_equals($sourceHash, (string) $form->get('homeEditorSourceHash')->getData())) {
            $form->addError(new \Symfony\Component\Form\FormError('L’accueil a changé depuis l’ouverture du formulaire. Rechargez-le avant de réessayer.'));
        }


        if ($form->isSubmitted() && $form->isValid()) {
            $heroImageFile = $form->get('heroImageFile')->getData();
            if ($heroImageFile) {
                try {
                    $oldPath = $page->getHeroImage();
                    $page->setHeroImage($uploadManager->upload($heroImageFile, 'pages/hero'));

                } catch (FileException $exception) {
                    $this->addFlash('danger', 'Impossible d\'envoyer l\'image hero.');

                    return $this->redirectToRoute('admin_home_content');
                }
            }

            $this->saveHomeEditorialState($page);
            $entityManager->flush();
            $this->addFlash('success', 'Contenu de la home mis à jour.');

            return $this->redirectToRoute('admin_home_content');
        }

        return $this->render('admin/home/content.html.twig', [
            'form' => $form,
            'page' => $page,
            'editorMode' => 'home',
            'practices' => [],
        ]);
    }

    #[Route('/practices', name: 'admin_home_practices', methods: ['GET', 'POST'])]
    public function practices(HomeSectionRepository $repository, PracticeRepository $practiceRepository, EntityManagerInterface $entityManager, Request $request, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
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

        $formData = $translationSynchronizer->homeSectionFormData($section);
        $form = $this->createForm(HomeSectionType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
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
            $translationSynchronizer->saveHomeSectionFromForm($section, $formData);
            $entityManager->flush();
            $this->addFlash('success', 'Sélection des practices mise à jour.');

            return $this->redirectToRoute('admin_home_practices');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $translationSynchronizer->saveHomeSectionFromForm($section, $formData);
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
    public function projects(HomeSectionRepository $repository, ProjetRepository $projetRepository, EntityManagerInterface $entityManager, Request $request, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
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

        $formData = $translationSynchronizer->homeSectionFormData($section);
        $form = $this->createForm(HomeSectionType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
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
            $translationSynchronizer->saveHomeSectionFromForm($section, $formData);
            $entityManager->flush();
            $this->addFlash('success', 'Sélection des projets mise à jour.');

            return $this->redirectToRoute('admin_home_projects');
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $translationSynchronizer->saveHomeSectionFromForm($section, $formData);
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
    public function awards(HomeSectionRepository $repository, HomeAwardItemRepository $awardRepository, EntityManagerInterface $entityManager, Request $request, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
    {
        $section = $repository->findOneBy(['slug' => 'awards']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('awards');
            $entityManager->persist($section);
        }

        $formData = $translationSynchronizer->homeSectionFormData($section);
        $form = $this->createForm(HomeAwardsSectionType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $translationSynchronizer->saveHomeSectionFromForm($section, $formData);
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

    private function loadHomeEditorialState(SitePage $page): void
    {
        $fr = $page->getTranslation('fr');
        if (!$fr) { return; }
        $payload = $fr->getStructuredData()['homePage'] ?? $this->decodeStructuredPayload($fr->getBodyHtml());
        $page->setTitle($fr->getSeoTitle() ?: $fr->getTitle());
        $page->setMetaDescription($fr->getSeoDescription());
        $page->setHeroBadge($fr->getHeroBadge());
        $page->setHeroTitle($fr->getHeroTitle());
        $page->setHeroIntro($fr->getHeroIntro());
        $page->setHeroSideHtml($fr->getHeroSideHtml());
        $page->setBodyHtml($this->encodeStructuredPayload($payload));
    }

    private function homeEditorialHash(SitePage $page): string
    {
        $fr = $page->getTranslation('fr');
        return hash('sha256', serialize([$page->getHeroImage(), $fr?->getTitle(), $fr?->getSeoTitle(), $fr?->getSeoDescription(), $fr?->getHeroTitle(), $fr?->getHeroIntro(), $fr?->getHeroBadge(), $fr?->getHeroSideHtml(), $fr?->getStructuredData(), $fr?->getBodyHtml()]));
    }

    private function saveHomeEditorialState(SitePage $page): void
    {
        $fr = $page->getTranslation('fr');
        if (!$fr) { throw new \LogicException('La traduction française de l’accueil est nécessaire.'); }
        $structured = $fr->getStructuredData() ?? [];
        $structured['homePage'] = $this->decodeStructuredPayload($page->getBodyHtml());
        if ($page->getTitle() !== ($fr->getSeoTitle() ?: $fr->getTitle())) {
            $fr->setTitle((string) $page->getTitle());
            $fr->setSeoTitle($page->getTitle());
        }
        $fr->setSeoDescription($page->getMetaDescription());
        $fr->setHeroBadge($page->getHeroBadge());
        $fr->setHeroTitle($page->getHeroTitle());
        $fr->setHeroIntro($page->getHeroIntro());
        $fr->setHeroSideHtml($page->getHeroSideHtml());
        $fr->setBodyHtml($page->getBodyHtml());
        $fr->setStructuredData($structured);
        $fr->setSourceContentHash((new \App\Service\TranslationSourceHasher())->hashSitePage($page));
        $fr->setSourceUpdatedAt(new \DateTimeImmutable());
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
