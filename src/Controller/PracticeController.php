<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\Projet;
use App\Entity\SitePageTranslation;
use App\Entity\Team;
use App\Form\EmailType;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Repository\ProjetRepository;
use App\Repository\MetierRepository;
use App\Repository\EmailRepository;
use App\Repository\TeamRepository;
use App\Repository\HomeSectionRepository;
use App\Repository\ContentItemRepository;
use App\Repository\LegalPageRepository;
use App\Repository\SitePageRepository;
use App\Service\PublicSitePageResolver;
use App\Service\SeoGeoInternalLinkService;
use App\Service\SitePageFaqParser;
use App\Service\LocalizedContentResolver;
use App\Service\I18n\LocalizedUrlGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Middleware\XRobotsTagMiddleware;

class PracticeController extends AbstractController
{
    public function __construct(
        private readonly PublicSitePageResolver $publicSitePageResolver,
        private readonly LocalizedContentResolver $localizedContentResolver,
        private readonly LocalizedUrlGenerator $localizedUrlGenerator,
    )
    {
    }

    #[Route('/', name: 'index', options: ["sitemap" => true])]
    public function index(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier,
        HomeSectionRepository $homeSectionRepository,
        \App\Repository\HomeAwardItemRepository $homeAwardRepository,
        ContentItemRepository $contentItemRepository,
        SitePageRepository $sitePageRepository,
        Request $request
    ): Response {
        return $this->renderHomePage(
            $repopractice,
            $reposervices,
            $repoprojet,
            $repometier,
            $homeSectionRepository,
            $homeAwardRepository,
            $contentItemRepository,
            $sitePageRepository,
            SitePageTranslation::LOCALE_FR
        );
    }

    #[Route('/{_locale}', name: 'localized_homepage', requirements: ['_locale' => 'en|es'], methods: ['GET'], priority: 100)]
    public function localizedIndex(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier,
        HomeSectionRepository $homeSectionRepository,
        \App\Repository\HomeAwardItemRepository $homeAwardRepository,
        ContentItemRepository $contentItemRepository,
        SitePageRepository $sitePageRepository,
        Request $request,
        string $_locale
    ): Response {
        SitePageTranslation::assertSupportedLocale($_locale);
        $request->setLocale($_locale);

        return $this->renderHomePage(
            $repopractice,
            $reposervices,
            $repoprojet,
            $repometier,
            $homeSectionRepository,
            $homeAwardRepository,
            $contentItemRepository,
            $sitePageRepository,
            $_locale
        );
    }

    private function renderHomePage(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier,
        HomeSectionRepository $homeSectionRepository,
        \App\Repository\HomeAwardItemRepository $homeAwardRepository,
        ContentItemRepository $contentItemRepository,
        SitePageRepository $sitePageRepository,
        string $locale
    ): Response {
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $projets = $repoprojet->findAll();
        $metiers = $repometier->findAll();
        $homeHeroMetiers = $this->buildHomeHeroMetiers($repometier->findHomeHeroCandidates());
        $featuredPractices = $repopractice->findBy(['featuredHome' => true]);
        usort($featuredPractices, static function ($a, $b) {
            $rankA = $a->getFeaturedHomeRank() ?? 9999;
            $rankB = $b->getFeaturedHomeRank() ?? 9999;
            if ($rankA === $rankB) {
                return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
            }
            return $rankA <=> $rankB;
        });
        $homePractices = $this->localizePractices(array_slice($featuredPractices, 0, 4), $locale);
        [$featuredHomeProjects] = $this->resolveFeaturedProjects($repoprojet);
        $homeProjects = $this->buildProjectCards($featuredHomeProjects, $this->buildProjectImagePool($projets), $locale);

        $homePracticesSection = $this->localizeHomeSection($homeSectionRepository->findOneBy(['slug' => 'practices']), $locale);
        $homeHeroSection = $this->localizeHomeSection($homeSectionRepository->findOneBy(['slug' => 'hero']), $locale);
        $homeProjectsSection = $this->localizeHomeSection($homeSectionRepository->findOneBy(['slug' => 'projects']), $locale);
        $homeAwardsSection = $this->localizeHomeSection($homeSectionRepository->findOneBy(['slug' => 'awards']), $locale);
        $homeAwards = $homeAwardRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']);
        $flashInfo = $contentItemRepository->findOneBy([], ['id' => 'DESC']);
        $homePageEntity = $sitePageRepository->findOneBy(['slug' => 'home']);
        $latestResources = array_values(array_filter(array_map(
            fn (\App\Entity\SitePage $page): ?array => $this->buildHomeResourceCard($page),
            array_slice($sitePageRepository->findResourceArticles(), 0, 2)
        )));

        return $this->render('index.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'projets' => $projets,
            'metiers' => $metiers,
            'homePage' => $this->publicSitePageResolver->getHomePage($locale),
            'homeHeroMetiers' => $homeHeroMetiers,
            'homePractices' => $homePractices,
            'homeProjects' => $homeProjects,
            'homePracticesSection' => $homePracticesSection,
            'homeHeroSection' => $homeHeroSection,
            'homeProjectsSection' => $homeProjectsSection,
            'homeAwardsSection' => $homeAwardsSection,
            'homeAwards' => $homeAwards,
            'latestResources' => $latestResources,
            'flashInfo' => $flashInfo,
            'localizedAlternates' => $homePageEntity ? $this->localizedUrlGenerator->sitePageAlternates($homePageEntity) : null,
            'canonicalPath' => $homePageEntity ? $this->localizedUrlGenerator->sitePagePath($homePageEntity, $locale) : ($locale === SitePageTranslation::LOCALE_FR ? '/' : '/'.$locale),
            'pract' => '',
        ]);
    }

    


    #[Route('/mentions-legales', name: 'discloser')]
    #[Route('/en/legal-notice', name: 'discloser_en', methods: ['GET'])]
    #[Route('/es/aviso-legal', name: 'discloser_es', methods: ['GET'])]
    public function discloser(
        Request $request,
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
        ): Response
    {
        $locale = $this->resolveLegalLocale($request);
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $legalPage = $this->localizeLegalPage($legalPageRepository->findOneBy(['slug' => 'mentions-legales']), $locale);
        return $this->render('page-terms.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'legalPage' => $legalPage,
            'pract' => '',
        ]);
    }

    #[Route('/charte-ia', name: 'charte_ia', methods: ['GET'])]
    #[Route('/en/ai-charter', name: 'charte_ia_en', methods: ['GET'])]
    #[Route('/es/carta-ia', name: 'charte_ia_es', methods: ['GET'])]
    public function charteIa(
        Request $request,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        LegalPageRepository $legalPageRepository
    ): Response {
        $locale = $this->resolveLegalLocale($request);
        $request->setLocale($locale);
        return $this->render('charte-ia.html.twig', [
            'practices' => $this->localizePractices($practiceRepository->findAll(), $locale),
            'services' => $this->localizeServices($servicesRepository->findAll(), $locale),
            'legalPage' => $this->localizeLegalPage($legalPageRepository->findOneBy(['slug' => 'charte-ia']), $locale),
            'pract' => '',
        ]);
    }

    #[Route('/{_locale}/{localizedSlug}', name: 'localized_apropos', requirements: ['_locale' => 'en|es', 'localizedSlug' => 'about|about-us|quienes-somos'], methods: ['GET'], priority: 80)]
    #[Route('/a-propos', name: 'apropos', options: ["sitemap" => true])]
    public function apropos(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        TeamRepository $repoteam,
        SitePageRepository $sitePageRepository,
        Request $request,
        ?string $_locale = SitePageTranslation::LOCALE_FR,
        ): Response
    {
        $locale = $_locale ?: SitePageTranslation::LOCALE_FR;
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $pageEntity = $sitePageRepository->findOneBy(['slug' => 'apropos']);

        return $this->render('about.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'teamPreview' => $this->buildTeamProfiles($repoteam->findAll(), $locale),
            'page' => $this->publicSitePageResolver->getEditorialPage('apropos', $locale),
            'localizedAlternates' => $pageEntity ? $this->localizedUrlGenerator->sitePageAlternates($pageEntity) : null,
            'canonicalPath' => $pageEntity ? $this->localizedUrlGenerator->sitePagePath($pageEntity, $locale) : null,
            'pract' => '',
        ]);
    }

    #[Route('/{_locale}/{localizedSlug}', name: 'localized_contact', requirements: ['_locale' => 'en|es', 'localizedSlug' => 'contact|contacto'], methods: ['GET'], priority: 80)]
    #[Route('/contact', name: 'contact', options: ["sitemap" => true])]
    public function contact(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        SitePageRepository $sitePageRepository,
        Request $request,
        ?string $_locale = SitePageTranslation::LOCALE_FR,
    ): Response
    {
        $locale = $_locale ?: SitePageTranslation::LOCALE_FR;
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $pageEntity = $sitePageRepository->findOneBy(['slug' => 'contact']);

        return $this->render('contact.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('contact', $locale),
            'localizedAlternates' => $pageEntity ? $this->localizedUrlGenerator->sitePageAlternates($pageEntity) : null,
            'canonicalPath' => $pageEntity ? $this->localizedUrlGenerator->sitePagePath($pageEntity, $locale) : null,
            'pract' => '',
        ]);
    }

    #[Route('/services', name: 'services_index', options: ["sitemap" => true])]
    public function servicesIndex(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response
    {
        return $this->render('services-index.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $this->localizePractices($practiceRepository->findAll()),
            'services' => $this->localizeServices($servicesRepository->findAll()),
            'page' => $this->publicSitePageResolver->getEditorialPage('services'),
            'pract' => '',
        ]);
    }

    #[Route('/{_locale}/{localizedSlug}', name: 'localized_projets', requirements: ['_locale' => 'en|es', 'localizedSlug' => 'projects|proyectos'], methods: ['GET'], priority: 80)]
    #[Route('/projets', name: 'projets', options: ["sitemap" => true])]
    public function projets(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier,
        SitePageRepository $sitePageRepository,
        Request $request,
        ?string $_locale = SitePageTranslation::LOCALE_FR,
    ): Response {
        $locale = $_locale ?: SitePageTranslation::LOCALE_FR;
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $projets = $repoprojet->findAll();
        $metiers = $repometier->findAll();
        $pageEntity = $sitePageRepository->findOneBy(['slug' => 'projets']);

        $importedProjects = array_values(array_filter($projets, static fn (Projet $projet) => $projet->getExternalId() !== null));
        $historicalProjects = array_values(array_filter($projets, static fn (Projet $projet) => $projet->getExternalId() === null));
        $projectPool = $importedProjects !== [] ? $importedProjects : $projets;

        [$featuredProjects, $featuredIds] = $this->resolveFeaturedProjectsFromCollection($projectPool);
        $imagePool = $this->buildProjectImagePool($projets);

        $perPage = 12;
        $miniProjectsAll = array_values(array_filter($projectPool, static fn (Projet $projet) => !in_array($projet->getId(), $featuredIds, true)));
        usort($miniProjectsAll, [$this, 'sortProjectsForListing']);
        $miniProjects = array_slice($miniProjectsAll, 0, $perPage);
        $hasMoreMini = count($miniProjectsAll) > $perPage;

        return $this->render('projets.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'projets' => $projets,
            'page' => $this->publicSitePageResolver->getEditorialPage('projets', $locale),
            'featuredProjects' => $this->buildProjectCards($featuredProjects, $imagePool, $locale),
            'miniProjects' => $this->buildProjectCards($miniProjects, $imagePool, $locale),
            'miniHasMore' => $hasMoreMini,
            'miniNextPage' => 2,
            'metiers' => $metiers,
            'localizedAlternates' => $pageEntity ? $this->localizedUrlGenerator->sitePageAlternates($pageEntity) : null,
            'canonicalPath' => $pageEntity ? $this->localizedUrlGenerator->sitePagePath($pageEntity, $locale) : null,
            'pract' => '',
        ]);
    }

    #[Route('/projets/more', name: 'projets_more', methods: ['GET'])]
    public function projetsMore(ProjetRepository $repoprojet, Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 12;
        $projects = $repoprojet->findAll();
        $importedProjects = array_values(array_filter($projects, static fn (Projet $projet) => $projet->getExternalId() !== null));
        $projectPool = $importedProjects !== [] ? $importedProjects : $projects;
        $imagePool = $this->buildProjectImagePool($projects);
        [$featuredProjects, $featuredIds] = $this->resolveFeaturedProjectsFromCollection($projectPool);
        $miniProjectsAll = array_values(array_filter($projectPool, static fn (Projet $projet) => !in_array($projet->getId(), $featuredIds, true)));
        usort($miniProjectsAll, [$this, 'sortProjectsForListing']);
        $offset = ($page - 1) * $perPage;
        $miniProjects = array_slice($miniProjectsAll, $offset, $perPage);
        $hasMoreMini = count($miniProjectsAll) > ($offset + $perPage);

        $html = $this->renderView('projets/_mini_cards.html.twig', [
            'miniProjects' => $this->buildProjectCards($miniProjects, $imagePool),
        ]);

        return new JsonResponse([
            'html' => $html,
            'nextPage' => $page + 1,
            'hasMore' => $hasMoreMini,
        ]);
    }

    private function resolveFeaturedProjects(ProjetRepository $repository): array
    {
        return $this->resolveFeaturedProjectsFromCollection($repository->findAll());
    }

    /**
     * @param Projet[] $projects
     * @return array{0: array<int, Projet>, 1: array<int, int|null>}
     */
    private function resolveFeaturedProjectsFromCollection(array $projects): array
    {
        $featuredProjects = array_values(array_filter($projects, static function (Projet $projet) {
            return $projet->isFeaturedProjects();
        }));
        usort($featuredProjects, [$this, 'sortFeaturedProjects']);

        if (count($featuredProjects) === 0) {
            usort($projects, [$this, 'sortProjectsForListing']);
            $featuredProjects = array_slice($projects, 0, 6);
        } else {
            $featuredProjects = array_slice($featuredProjects, 0, 6);
        }

        $featuredIds = array_map(static fn (Projet $projet) => $projet->getId(), $featuredProjects);

        return [$featuredProjects, $featuredIds];
    }

    private function sortFeaturedProjects(Projet $a, Projet $b): int
    {
        $rankA = $a->getFeaturedProjectsRank() ?? 9999;
        $rankB = $b->getFeaturedProjectsRank() ?? 9999;
        if ($rankA === $rankB) {
            return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
        }

        return $rankA <=> $rankB;
    }

    private function sortProjectsForListing(Projet $a, Projet $b): int
    {
        $externalA = $a->getExternalId() ?? '';
        $externalB = $b->getExternalId() ?? '';
        if ($externalA !== '' && $externalB !== '') {
            $scoreA = $this->buildEditorialSortScore($a);
            $scoreB = $this->buildEditorialSortScore($b);

            foreach ($scoreA as $index => $value) {
                $comparison = $value <=> $scoreB[$index];
                if ($comparison !== 0) {
                    return $comparison;
                }
            }

            return 0;
        }

        return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
    }

    private function buildEditorialSortScore(Projet $project): array
    {
        $externalId = $project->getExternalId() ?? '';
        $metadata = $project->getMetadata();

        return [
            $this->projectTypeBucket($externalId),
            $this->publicationBucket((string) $project->getPublicationStatus()),
            $this->linkedFeaturedBucket((string) ($metadata['linked_featured_project'] ?? '')),
            $this->contentTypeBucket((string) ($metadata['content_type'] ?? '')),
            $this->waveBucket((string) ($metadata['priority_wave'] ?? '')),
            $this->proofBucket((string) $project->getProofStatus()),
            -$this->extractProjectOrder($externalId),
        ];
    }

    private function projectTypeBucket(string $externalId): int
    {
        return match (true) {
            str_starts_with($externalId, 'PH-') => 0,
            str_starts_with($externalId, 'ACT-') => 1,
            default => 9,
        };
    }

    private function publicationBucket(string $publicationStatus): int
    {
        $value = mb_strtolower(trim($publicationStatus));

        return match (true) {
            str_contains($value, 'déjà cité') => 0,
            str_contains($value, 'référence publiable') => 1,
            str_contains($value, 'à valider avant publication') => 2,
            str_contains($value, 'base interne') => 5,
            $value === '' => 6,
            default => 3,
        };
    }

    private function linkedFeaturedBucket(string $linkedFeaturedProject): int
    {
        return trim($linkedFeaturedProject) !== '' ? 0 : 1;
    }

    private function contentTypeBucket(string $contentType): int
    {
        $value = mb_strtolower(trim($contentType));

        return match (true) {
            str_contains($value, 'cas client détaillé') => 0,
            str_contains($value, 'carte catalogue') => 1,
            str_contains($value, 'référence courte') => 2,
            str_contains($value, 'base interne') => 5,
            $value === '' => 4,
            default => 3,
        };
    }

    private function waveBucket(string $wave): int
    {
        if (preg_match('/vague\s+(\d+)/i', $wave, $matches) === 1) {
            return (int) $matches[1];
        }

        return 9;
    }

    private function proofBucket(string $proofStatus): int
    {
        $value = mb_strtolower(trim($proofStatus));

        return match (true) {
            str_contains($value, 'documenté') => 0,
            str_contains($value, 'prouv') => 0,
            str_contains($value, 'facture') => 1,
            str_contains($value, 'historique') => 2,
            str_contains($value, 'à enrichir') => 4,
            $value === '' => 5,
            default => 3,
        };
    }

    private function extractProjectOrder(string $externalId): int
    {
        if (preg_match('/(\d+)$/', $externalId, $matches) === 1) {
            return (int) $matches[1];
        }

        return 9999;
    }

    /**
     * @param Projet[] $projects
     * @return array<int, string>
     */
    private function buildProjectImagePool(array $projects): array
    {
        $images = [];

        foreach ($projects as $project) {
            $image = $project->getImageHero() ?: $project->getImage();
            if (!is_string($image) || trim($image) === '') {
                continue;
            }
            $images[] = trim($image);
        }

        return array_values(array_unique($images));
    }

    /**
     * @param Projet[] $projects
     * @param string[] $imagePool
     * @return array<int, array<string, mixed>>
     */
    private function buildProjectCards(array $projects, array $imagePool, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $poolOffset = 0;
        if ($projects !== [] && $imagePool !== []) {
            $firstKey = $projects[0]->getExternalId() ?: $projects[0]->getSlug() ?: (string) $projects[0]->getId();
            $poolOffset = abs(crc32($firstKey)) % count($imagePool);
        }

        $cards = [];
        foreach ($projects as $position => $project) {
            $projectService = $project->getServices()->first();
            $metadata = $project->getMetadata();
            $projectContent = $this->localizedContentResolver->getProjetView($project, $locale);
            $serviceContent = $projectService instanceof \App\Entity\Services ? $this->localizedContentResolver->getServiceView($projectService, $locale) : null;
            $title = $this->buildProjectCardTitle($projectContent);
            $excerpt = $this->buildProjectCardExcerpt($projectContent, $metadata, $locale);
            if ($locale !== SitePageTranslation::LOCALE_FR && ($title === '' || $excerpt === '')) {
                continue;
            }

            $cards[] = [
                'href' => $locale === SitePageTranslation::LOCALE_FR ? $this->resolveProjectCardHref($project, $projectService) : $this->localizedContactPath($locale),
                'image' => $this->resolveProjectCardImage($project, $imagePool, $poolOffset + $position),
                'title' => $title,
                'eyebrow' => $locale === SitePageTranslation::LOCALE_FR ? $this->buildProjectCardEyebrow($project, $metadata) : $serviceContent?->getDesignation(),
                'meta' => $projectContent->getTerritory() ?: ($serviceContent ? $serviceContent->getDesignation() : null),
                'period' => $projectContent->getPeriodLabel(),
                'excerpt' => $excerpt,
                'index' => $project->getFeaturedProjectsRank(),
            ];
        }

        return $cards;
    }

    private function resolveProjectCardHref(Projet $project, mixed $projectService): string
    {
        $publicUrl = trim((string) ($project->getPublicUrl() ?? ''));
        if ($publicUrl !== '' && !str_starts_with($publicUrl, '/projets/')) {
            return $publicUrl;
        }

        if ($projectService && $projectService->getPractice()) {
            return $this->generateUrl('service', [
                'practice' => $projectService->getPractice()->getSlug(),
                'slug' => $projectService->getSlug(),
            ]);
        }

        return $this->generateUrl('contact');
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function buildProjectCardEyebrow(Projet $project, array $metadata): ?string
    {
        $subPractice = trim((string) ($metadata['sub_practice'] ?? ''));
        if ($subPractice !== '') {
            return $subPractice;
        }

        $practice = trim((string) ($metadata['practice'] ?? ''));
        if ($practice !== '') {
            return $practice;
        }

        if ($project->getMetier() !== null) {
            return trim(strip_tags((string) $project->getMetier()->getDesignation()));
        }

        return null;
    }

    private function buildProjectCardTitle(mixed $project): string
    {
        $designation = trim((string) $project->getDesignation());
        if ($project->getExternalId() !== null && str_contains($designation, ' – ')) {
            $parts = explode(' – ', $designation, 2);

            return trim($parts[1]) !== '' ? trim($parts[1]) : $designation;
        }

        return $designation;
    }

    /**
     * @param array<string, mixed> $metadata
     */
    private function buildProjectCardExcerpt(mixed $project, array $metadata, string $locale = SitePageTranslation::LOCALE_FR): string
    {
        $editorialAngle = $locale === SitePageTranslation::LOCALE_FR ? trim((string) ($metadata['editorial_angle'] ?? '')) : '';
        if ($editorialAngle !== '') {
            return $editorialAngle;
        }

        $shortDescription = trim((string) ($project->getShortDescription() ?? ''));
        if ($shortDescription !== '') {
            return $shortDescription;
        }

        $description = trim(strip_tags((string) ($project->getDescription() ?? '')));
        if ($description !== '') {
            $sentences = preg_split('/(?<=[.!?])\s+/u', $description) ?: [];
            if (($sentences[0] ?? '') !== '') {
                return trim((string) $sentences[0]);
            }
        }

        return $locale === SitePageTranslation::LOCALE_FR ? 'Projet de transformation, de cadrage ou de mise en œuvre mené par les équipes OLING.' : '';
    }

    private function localizedContactPath(string $locale): string
    {
        return match ($locale) {
            SitePageTranslation::LOCALE_EN => '/en/contact',
            SitePageTranslation::LOCALE_ES => '/es/contacto',
            default => $this->generateUrl('contact'),
        };
    }

    /**
     * @param string[] $imagePool
     */
    private function resolveProjectCardImage(Projet $project, array $imagePool, int $fallbackIndex): string
    {
        $image = $project->getImageHero() ?: $project->getImage();
        if (is_string($image) && trim($image) !== '') {
            return trim($image);
        }

        if ($imagePool !== []) {
            return $imagePool[$fallbackIndex % count($imagePool)];
        }

        return '/img/1920x1080/img5.jpg';
    }

    private function buildHomeResourceCard(\App\Entity\SitePage $page): ?array
    {
        $content = $this->localizedContentResolver->getFrenchPublicView($page);
        $storedSlug = $content->getSlug();
        if (!str_starts_with($storedSlug, 'ressource-')) {
            return null;
        }

        $publicSlug = substr($storedSlug, strlen('ressource-'));
        if ($publicSlug === false || $publicSlug === '') {
            return null;
        }

        return [
            'slug' => $publicSlug,
            'title' => (string) ($content->getHeroTitle() ?: $content->getTitle()),
            'intro' => trim((string) ($content->getHeroIntro() ?: '')),
            'publicationDate' => $content->getPublicationDate(),
        ];
    }

    /**
     * @param \App\Entity\Metier[] $metiers
     * @return array<int, array<string, string>>
     */
    private function buildHomeHeroMetiers(array $metiers): array
    {
        $items = [];

        foreach ($metiers as $metier) {
            $image = $metier->getImageHero() ?: $metier->getImage();
            if (!is_string($image) || trim($image) === '') {
                continue;
            }

            $designation = trim((string) $metier->getDesignation());
            $items[] = [
                'designation' => $designation,
                'image' => trim($image),
                'intro' => trim((string) ($metier->getHomeHeroIntro() ?: '')),
                'text1' => trim((string) ($metier->getHomeHeroText1() ?: $designation)),
                'text2' => trim((string) ($metier->getHomeHeroText2() ?: '')),
            ];
        }

        if (count($items) > 1) {
            shuffle($items);
        }

        return $items;
    }

    #[Route('/amoa-si', name: 'amoa_si', options: ["sitemap" => true])]
    public function amoaSi(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
    ): Response
    {
        $practice = $repopractice->findOneBy(['slug' => 'consulting']);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->renderPracticeHome($practice, $repopractice, $reposervices);
    }

   

    #[Route('/a-propos/metiers', name: 'metiers', options: ["sitemap" => true])]
    public function metiers(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
    ): Response
    {
        $practices = $this->localizePractices($repopractice->findAll());
        $services = $this->localizeServices($reposervices->findAll());
        return $this->render('metiers.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('metiers'),
            'sectorCatalog' => $this->publicSitePageResolver->getSectorCatalogEntries(),
            'sectorPages' => $this->publicSitePageResolver->getSectorPages(),
            'pract' => '',
        ]);
    }

    #[Route('/{_locale}/{localizedSlug}', name: 'localized_team', requirements: ['_locale' => 'en|es', 'localizedSlug' => 'team|equipo'], methods: ['GET'], priority: 80)]
    #[Route('/a-propos/team', name: 'team', options: ["sitemap" => true])]
    public function team(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        TeamRepository $repoteam,
        SitePageRepository $sitePageRepository,
        Request $request,
        ?string $_locale = SitePageTranslation::LOCALE_FR,
    ): Response
    {
        $locale = $_locale ?: SitePageTranslation::LOCALE_FR;
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $team = $this->buildTeamProfiles($repoteam->findAll(), $locale);
        $pageEntity = $sitePageRepository->findOneBy(['slug' => 'team']);

        return $this->render('team.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'team' => $team,
            'teamSchemas' => $this->buildTeamSchemas($team),
            'page' => $this->publicSitePageResolver->getEditorialPage('team', $locale),
            'localizedAlternates' => $pageEntity ? $this->localizedUrlGenerator->sitePageAlternates($pageEntity) : null,
            'canonicalPath' => $pageEntity ? $this->localizedUrlGenerator->sitePagePath($pageEntity, $locale) : null,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/client', name: 'client', options: ["sitemap" => true])]
    public function client(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
    ): Response
    {
        $practices = $this->localizePractices($repopractice->findAll());
        $services = $this->localizeServices($reposervices->findAll());
        return $this->render('client.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('client'),
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/rse', name: 'rse', options: ["sitemap" => true])]
    public function rse(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
    ): Response
    {
        $practices = $this->localizePractices($repopractice->findAll());
        $services = $this->localizeServices($reposervices->findAll());
        return $this->render('rse.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('rse'),
            'pract' => '',
        ]);
    }
    #[Route('/a-propos/politiquergpd', name: 'polrgpd')]
    #[Route('/en/privacy-policy', name: 'polrgpd_en', methods: ['GET'])]
    #[Route('/es/politica-rgpd', name: 'polrgpd_es', methods: ['GET'])]
    public function polrgpd(
        Request $request,
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
    ): Response
    {
        $locale = $this->resolveLegalLocale($request);
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $legalPage = $this->localizeLegalPage($legalPageRepository->findOneBy(['slug' => 'polrgpd']), $locale);
        return $this->render('polrgpd.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'legalPage' => $legalPage,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/politiquesecurite', name: 'polsecurite')]
    #[Route('/en/information-security-policy', name: 'polsecurite_en', methods: ['GET'])]
    #[Route('/es/politica-seguridad-informacion', name: 'polsecurite_es', methods: ['GET'])]
    public function polsecurite(
        Request $request,
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
    ): Response
    {
        $locale = $this->resolveLegalLocale($request);
        $request->setLocale($locale);
        $practices = $this->localizePractices($repopractice->findAll(), $locale);
        $services = $this->localizeServices($reposervices->findAll(), $locale);
        $legalPage = $this->localizeLegalPage($legalPageRepository->findOneBy(['slug' => 'polsecurite']), $locale);
        return $this->render('polsecu.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'legalPage' => $legalPage,
            'pract' => '',
        ]);
    }
    

    #[Route('/add-email', name: 'add_email')]
    public function addEmail(Request $request, EntityManagerInterface $entityManager)
    {
        // Récupérer les données du formulaire
        $email = $request->request->get('email');

        // Vérifier si l'email existe déjà en base de données
        $emailExist = $entityManager->getRepository(Email::class)->findOneBy(['email' => $email]);

        if ($emailExist) {
            // Si l'email existe déjà, retourner une réponse JSON avec une erreur
            $response = new JsonResponse();
            $response->setData([
                'success' => false,
                'message' => 'Cet email existe déjà',
            ]);
            return $response;
        }

        // Vérifier que l'email est valide
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Si l'email n'est pas valide, retourner une réponse JSON avec une erreur
            $response = new JsonResponse();
            $response->setData([
                'success' => false,
                'message' => 'L\'email n\'est pas valide',
            ]);
            return $response;
        }

        // Créer une nouvelle instance de Email
        $newEmail = new Email();
        $newEmail->setEmail($email);

        // Enregistrer l'email dans la base de données
        $entityManager->persist($newEmail);
        $entityManager->flush();

        // Retourner une réponse JSON
        $response = new JsonResponse();
        $response->setData([
            'success' => true,
            'message' => 'Merci pour votre inscription. Vous allez bientôt recevoir nos newsletters',
        ]);

        return $response;
    }




    #[Route('/{practice}/{slug}', name: 'service', requirements: ['practice' => '(?!admin(?:/|$)|login(?:/|$)|logout(?:/|$)|uploads(?:/|$)|fr(?:/|$)|en(?:/|$)|es(?:/|$))[a-z0-9\\-]+'], priority: -10)]
    public function services(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        $slug,
        $practice
    ): Response {
        $service = $servicesRepository->findOneBy(['slug' => $slug]);

        if (!$service) {
            throw $this->createNotFoundException('Le service n\'existe pas.');
        }

        $servicePractice = $service->getPractice();
        $expectedPracticeSlug = $servicePractice?->getSlug();

        if (!$expectedPracticeSlug) {
            throw $this->createNotFoundException('Le service n\'est rattaché à aucune practice publiée.');
        }

        if ($practice !== $expectedPracticeSlug) {
            return $this->redirectToRoute('service', [
                'practice' => $expectedPracticeSlug,
                'slug' => $slug,
            ], 301);
        }

        $practiceEntity = $practiceRepository->findOneBy(['slug' => $practice]);

        if (!$practiceEntity) {
            throw $this->createNotFoundException('La practice n\'existe pas.');
        }

        if ($servicePractice?->getId() !== $practiceEntity->getId()) {
            throw $this->createNotFoundException('Le service ne correspond pas à la practice demandée.');
        }

        $practices = $this->localizePractices($practiceRepository->findAll());
        $services = $this->localizeServices($servicesRepository->findAll());

        $serviceContent = $this->localizedContentResolver->getFrenchServiceView($service);
        if (empty($serviceContent->getIntroductionShort())) {
            return $this->redirectToRoute('index');
        }

        return $this->render('services.html.twig', [
            'controller_name' => 'PracticeController',
            'service' => $serviceContent,
            'serviceNarrative' => $serviceContent->getPublicNarrative(),
            'pract' => $practice,
            'practices' => $practices,
            'services' => $services,
        ]);
    }



    #[Route('/practice/consulting', name: 'practice_home_consulting', methods: ['GET'], priority: 10)]
    public function practiceHomeConsulting(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $practice = $practiceRepository->findOneBy(['slug' => 'consulting']);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->renderPracticeHome($practice, $practiceRepository, $servicesRepository);
    }

    #[Route('/practice/expertises-audit', name: 'practice_home_expertises_audit', methods: ['GET'], priority: 10)]
    public function practiceHomeExpertisesAudit(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $practice = $practiceRepository->findOneBy(['slug' => 'expertises-audit']);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->renderPracticeHome($practice, $practiceRepository, $servicesRepository);
    }

    #[Route('/practice/business-apps', name: 'practice_home_business_apps', methods: ['GET'], priority: 10)]
    public function practiceHomeBusinessApps(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $practice = $practiceRepository->findOneBy(['slug' => 'business-apps']);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->renderPracticeHome($practice, $practiceRepository, $servicesRepository);
    }

    #[Route('/practice/{slug}', name: 'practice_home', requirements: ['slug' => '(?!login$|logout$|admin$|uploads$|fr$|en$|es$)[a-z0-9\\-]+'], priority: 0)]
    public function practiceHome(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        $slug
    ): Response {
        if ($this->isAmoaAlias($slug)) {
            return $this->redirectToRoute('amoa_si', [], 301);
        }

        $practice = $practiceRepository->findOneBy(['slug' => $slug]);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->renderPracticeHome($practice, $practiceRepository, $servicesRepository);
    }

    #[Route('/{slug}', name: 'practice', requirements: ['slug' => '(?!login$|logout$|admin$|uploads$|fr$|en$|es$)[a-z0-9\\-]+'], priority: -10)]
    public function practices(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        $slug
    ): Response {
        if ($this->isAmoaAlias($slug)) {
            return $this->redirectToRoute('amoa_si', [], 301);
        }

        $practice = $practiceRepository->findOneBy(['slug' => $slug]);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        return $this->redirectToRoute('practice_home', ['slug' => $slug], 301);
    }

    private function renderPracticeHome(
        \App\Entity\Practice $practice,
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository
    ): Response {
        $practices = $this->localizePractices($practiceRepository->findAll());
        $services = $this->localizeServices($servicesRepository->findAll());

        $teamsMap = [];
        foreach ($practice->getServices() as $service) {
            foreach ($service->getTeams() as $team) {
                $teamsMap[$team->getId()] = $team;
            }
        }
        $teams = array_values($teamsMap);
        $projectsMap = [];
        foreach ($practice->getServices() as $service) {
            foreach ($service->getProjets() as $projet) {
                $projectsMap[$projet->getId()] = $projet;
            }
        }
        $projects = array_values($projectsMap);

        return $this->render('practice-home.html.twig', [
            'controller_name' => 'PracticeController',
            'practice' => $this->localizedContentResolver->getFrenchPracticeView($practice),
            'practiceNarrative' => $this->publicSitePageResolver->getPracticeNarrative($practice),
            'expertisePages' => $this->publicSitePageResolver->getExpertisePages(),
            'pract' => $practice->getSlug(),
            'practices' => $practices,
            'services' => $services,
            'teams' => $this->localizeTeams($teams),
            'projects' => $this->localizeProjects($projects),
        ]);
    }

    /**
     * @param Team[] $members
     * @return array<int, array<string, mixed>>
     */
    private function buildTeamProfiles(array $members, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $preview = [];
        foreach ($members as $member) {
            $memberView = $this->localizedContentResolver->getTeamView($member, $locale);
            $profile = $memberView->getPublicProfile();
            if (!is_array($profile) || $profile === []) {
                continue;
            }

            $preview[] = [
                'slug' => $profile['slug'] ?? $this->normalizeTeamName((string) $member->getNoncomplet()),
                'noncomplet' => $profile['displayName'] ?? $member->getNoncomplet(),
                'titre' => $memberView->getTitre() ?: ($profile['titre'] ?? ''),
                'shortcv' => $memberView->getShortcv() ?: ($profile['shortcv'] ?? ''),
                'areas' => $profile['areas'] ?? [],
                'organization' => $profile['relationshipText'] ?? '',
                'relationSchema' => $profile['relationSchema'] ?? 'affiliation',
                'linkedin' => $profile['linkedin'] ?? null,
                'publicationsUrl' => $profile['publicationsUrl'] ?? null,
                'photo' => $profile['photo'] ?? $member->getPhoto(),
            ];
        }

        return $preview;
    }

    /**
     * @param array<int, array<string, mixed>> $profiles
     * @return array<int, array<string, mixed>>
     */
    private function buildTeamSchemas(array $profiles): array
    {
        return array_map(static function (array $profile): array {
            $sameAs = [$profile['linkedin']];
            if ($profile['publicationsUrl']) {
                $sameAs[] = $profile['publicationsUrl'];
            }

            $schema = [
                '@type' => 'Person',
                '@id' => sprintf('https://oling.fr/a-propos/team#%s', $profile['slug']),
                'name' => $profile['noncomplet'],
                'jobTitle' => $profile['titre'],
                'url' => sprintf('https://oling.fr/a-propos/team#%s', $profile['slug']),
                'sameAs' => $sameAs,
                'knowsAbout' => array_column($profile['areas'], 'label'),
            ];

            $schema[$profile['relationSchema']] = [
                '@type' => 'Organization',
                '@id' => 'https://oling.fr/#professional-service',
                'name' => 'OLING Management et Technologie',
            ];

            return $schema;
        }, $profiles);
    }

    private function normalizeTeamName(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        $normalized = trim(mb_strtolower($value));
        $normalized = str_replace(
            ['é', 'è', 'ê', 'ë', 'à', 'â', 'ä', 'î', 'ï', 'ô', 'ö', 'ù', 'û', 'ü', 'ç'],
            ['e', 'e', 'e', 'e', 'a', 'a', 'a', 'i', 'i', 'o', 'o', 'u', 'u', 'u', 'c'],
            $normalized
        );

        return preg_replace('/\s+/', ' ', $normalized) ?? $normalized;
    }

    private function localizeLegalPage(?\App\Entity\LegalPage $page, string $locale = SitePageTranslation::LOCALE_FR): mixed
    {
        return $page ? $this->localizedContentResolver->getLegalPageView($page, $locale) : null;
    }

    private function resolveLegalLocale(Request $request): string
    {
        $route = (string) $request->attributes->get('_route');
        if (str_ends_with($route, '_en')) {
            return SitePageTranslation::LOCALE_EN;
        }
        if (str_ends_with($route, '_es')) {
            return SitePageTranslation::LOCALE_ES;
        }

        return SitePageTranslation::LOCALE_FR;
    }

    private function localizeHomeSection(?\App\Entity\HomeSection $section, string $locale = SitePageTranslation::LOCALE_FR): mixed
    {
        return $section ? $this->localizedContentResolver->getHomeSectionView($section, $locale) : null;
    }

    /**
     * @param \App\Entity\Practice[] $practices
     * @return array<int, mixed>
     */
    private function localizePractices(array $practices, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        return array_map(fn (\App\Entity\Practice $practice) => $this->localizedContentResolver->getPracticeView($practice, $locale), $practices);
    }

    /**
     * @param \App\Entity\Services[] $services
     * @return array<int, mixed>
     */
    private function localizeServices(array $services, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        return array_map(fn (\App\Entity\Services $service) => $this->localizedContentResolver->getServiceView($service, $locale), $services);
    }

    /**
     * @param Projet[] $projects
     * @return array<int, mixed>
     */
    private function localizeProjects(array $projects, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        return array_map(fn (Projet $project) => $this->localizedContentResolver->getProjetView($project, $locale), $projects);
    }

    /**
     * @param Team[] $teams
     * @return array<int, mixed>
     */
    private function localizeTeams(array $teams, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        return array_map(fn (Team $team) => $this->localizedContentResolver->getTeamView($team, $locale), $teams);
    }

    private function isAmoaAlias(string $slug): bool
    {
        return $slug === 'amoa-si';
    }





   
}
