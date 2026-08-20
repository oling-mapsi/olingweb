<?php

namespace App\Controller;

use App\Entity\Email;
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
use App\Service\PublicSiteConfig;
use App\Service\PublicSitePageResolver;
use App\Service\SeoGeoInternalLinkService;
use App\Service\SitePageFaqParser;
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
        private readonly PublicSiteConfig $publicSiteConfig,
        private readonly PublicSitePageResolver $publicSitePageResolver,
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
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
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
        $homePractices = array_slice($featuredPractices, 0, 4);
        [$homeProjects] = $this->resolveFeaturedProjects($repoprojet);

        $homePracticesSection = $homeSectionRepository->findOneBy(['slug' => 'practices']);
        $homeHeroSection = $homeSectionRepository->findOneBy(['slug' => 'hero']);
        $homeProjectsSection = $homeSectionRepository->findOneBy(['slug' => 'projects']);
        $homeAwardsSection = $homeSectionRepository->findOneBy(['slug' => 'awards']);
        $homeAwards = $homeAwardRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']);
        $flashInfo = $contentItemRepository->findOneBy([], ['id' => 'DESC']);
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
            'homePage' => $this->publicSitePageResolver->getHomePage(),
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
            'pract' => '',
        ]);
    }

    


    #[Route('/mentions-legales', name: 'discloser')]
    public function discloser(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $legalPage = $legalPageRepository->findOneBy(['slug' => 'mentions-legales']);
        return $this->render('page-terms.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'legalPage' => $legalPage,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos', name: 'apropos', options: ["sitemap" => true])]
    public function apropos(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        TeamRepository $repoteam,
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('about.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'teamPreview' => array_slice($repoteam->findAll(), 0, 3),
            'page' => $this->publicSitePageResolver->getEditorialPage('apropos'),
            'pract' => '',
        ]);
    }

    #[Route('/contact', name: 'contact', options: ["sitemap" => true])]
    public function contact(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('contact.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('contact'),
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
            'practices' => $practiceRepository->findAll(),
            'services' => $servicesRepository->findAll(),
            'page' => $this->publicSitePageResolver->getEditorialPage('services'),
            'pract' => '',
        ]);
    }

    #[Route('/projets', name: 'projets', options: ["sitemap" => true])]
    public function projets(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier
    ): Response {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $projets = $repoprojet->findAll();
        $metiers = $repometier->findAll();

        [$featuredProjects, $featuredIds] = $this->resolveFeaturedProjects($repoprojet);

        $perPage = 12;
        $miniQuery = $repoprojet->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');
        if (!empty($featuredIds)) {
            $miniQuery
                ->andWhere('p.id NOT IN (:featuredIds)')
                ->setParameter('featuredIds', $featuredIds);
        }
        $countQuery = clone $miniQuery;
        $totalMini = (int) $countQuery
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $miniProjects = $miniQuery
            ->setFirstResult(0)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
        $hasMoreMini = $totalMini > $perPage;

        return $this->render('projets.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'projets' => $projets,
            'page' => $this->publicSitePageResolver->getEditorialPage('projets'),
            'featuredProjects' => $featuredProjects,
            'miniProjects' => $miniProjects,
            'miniHasMore' => $hasMoreMini,
            'miniNextPage' => 2,
            'metiers' => $metiers,
            'pract' => '',
        ]);
    }

    #[Route('/projets/more', name: 'projets_more', methods: ['GET'])]
    public function projetsMore(ProjetRepository $repoprojet, Request $request): JsonResponse
    {
        $page = max(1, (int) $request->query->get('page', 1));
        $perPage = 12;
        [$featuredProjects, $featuredIds] = $this->resolveFeaturedProjects($repoprojet);

        $miniQuery = $repoprojet->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC');
        if (!empty($featuredIds)) {
            $miniQuery
                ->andWhere('p.id NOT IN (:featuredIds)')
                ->setParameter('featuredIds', $featuredIds);
        }
        $countQuery = clone $miniQuery;
        $totalMini = (int) $countQuery
            ->select('COUNT(p.id)')
            ->getQuery()
            ->getSingleScalarResult();
        $offset = ($page - 1) * $perPage;
        $miniProjects = $miniQuery
            ->setFirstResult($offset)
            ->setMaxResults($perPage)
            ->getQuery()
            ->getResult();
        $hasMoreMini = $totalMini > ($offset + $perPage);

        $html = $this->renderView('projets/_mini_cards.html.twig', [
            'miniProjects' => $miniProjects,
        ]);

        return new JsonResponse([
            'html' => $html,
            'nextPage' => $page + 1,
            'hasMore' => $hasMoreMini,
        ]);
    }

    private function resolveFeaturedProjects(ProjetRepository $repository): array
    {
        $featuredProjects = $repository->findBy(['featuredProjects' => true]);
        usort($featuredProjects, static function ($a, $b) {
            $rankA = $a->getFeaturedProjectsRank() ?? 9999;
            $rankB = $b->getFeaturedProjectsRank() ?? 9999;
            if ($rankA === $rankB) {
                return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
            }
            return $rankA <=> $rankB;
        });

        if (count($featuredProjects) === 0) {
            $featuredProjects = $repository->findBy([], ['id' => 'DESC'], 6);
        } else {
            $featuredProjects = array_slice($featuredProjects, 0, 6);
        }

        $featuredIds = array_map(static function ($projet) {
            return $projet->getId();
        }, $featuredProjects);

        return [$featuredProjects, $featuredIds];
    }

    private function buildHomeResourceCard(\App\Entity\SitePage $page): ?array
    {
        $storedSlug = (string) $page->getSlug();
        if (!str_starts_with($storedSlug, 'ressource-')) {
            return null;
        }

        $publicSlug = substr($storedSlug, strlen('ressource-'));
        if ($publicSlug === false || $publicSlug === '') {
            return null;
        }

        return [
            'slug' => $publicSlug,
            'title' => (string) ($page->getHeroTitle() ?: $page->getTitle()),
            'intro' => trim((string) ($page->getHeroIntro() ?: '')),
            'publicationDate' => $page->getPublicationDate(),
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
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('metiers.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $this->publicSitePageResolver->getEditorialPage('metiers'),
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/team', name: 'team', options: ["sitemap" => true])]
    public function team(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        TeamRepository $repoteam,
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $team = $repoteam->findAll();
        return $this->render('team.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'team' => $team,
            'page' => $this->publicSitePageResolver->getEditorialPage('team'),
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/client', name: 'client', options: ["sitemap" => true])]
    public function client(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
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
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('rse.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }
    #[Route('/a-propos/politiquergpd', name: 'polrgpd')]
    public function polrgpd(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $legalPage = $legalPageRepository->findOneBy(['slug' => 'polrgpd']);
        return $this->render('polrgpd.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'legalPage' => $legalPage,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/politiquesecurite', name: 'polsecurite')]
    public function polsecurite(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        LegalPageRepository $legalPageRepository
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $legalPage = $legalPageRepository->findOneBy(['slug' => 'polsecurite']);
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




    #[Route('/{practice}/{slug}', name: 'service', requirements: ['practice' => '(?!admin$|login$|logout$|uploads$)[a-z0-9\\-]+'], priority: -10)]
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

        $practices = $practiceRepository->findAll();
        $services = $servicesRepository->findAll();

        if (empty($service->getIntroductionShort())) {
            return $this->redirectToRoute('index');
        }

        return $this->render('services.html.twig', [
            'controller_name' => 'PracticeController',
            'service' => $service,
            'serviceNarrative' => $this->publicSiteConfig->getServiceNarrative($service),
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

    #[Route('/practice/{slug}', name: 'practice_home', requirements: ['slug' => '(?!login$|logout$|admin$|uploads$)[a-z0-9\\-]+'], priority: 0)]
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

    #[Route('/{slug}', name: 'practice', requirements: ['slug' => '(?!login$|logout$|admin$|uploads$)[a-z0-9\\-]+'], priority: -10)]
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
        $practices = $practiceRepository->findAll();
        $services = $servicesRepository->findAll();

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
            'practice' => $practice,
            'practiceNarrative' => $this->publicSiteConfig->getPracticeNarrative($practice),
            'expertisePages' => $this->publicSiteConfig->getExpertisePages(),
            'pract' => $practice->getSlug(),
            'practices' => $practices,
            'services' => $services,
            'teams' => $teams,
            'projects' => $projects,
        ]);
    }

    private function isAmoaAlias(string $slug): bool
    {
        return $slug === 'amoa-si';
    }





   
}
