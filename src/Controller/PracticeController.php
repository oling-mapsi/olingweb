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
    #[Route('/', name: 'index', options: ["sitemap" => true])]
    public function index(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        ProjetRepository $repoprojet,
        MetierRepository $repometier,
        \App\Repository\HomeCardRepository $homeCardRepository,
        HomeSectionRepository $homeSectionRepository,
        \App\Repository\HomeAwardItemRepository $homeAwardRepository,
        \App\Repository\HomeProjectKpiRepository $homeProjectKpiRepository,
        ContentItemRepository $contentItemRepository,
        Request $request
    ): Response {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $projets = $repoprojet->findAll();
        $metiers = $repometier->findAll();
        $homeCards = $homeCardRepository->findBy([], ['createdAt' => 'DESC'], 8);
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

        $homePracticesSection = $homeSectionRepository->findOneBy(['slug' => 'practices']);
        $homeHeroSection = $homeSectionRepository->findOneBy(['slug' => 'hero']);
        $homeProjectsSection = $homeSectionRepository->findOneBy(['slug' => 'projects']);
        $homeAwardsSection = $homeSectionRepository->findOneBy(['slug' => 'awards']);
        $homeAwards = $homeAwardRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']);
        $homeProjectKpis = $homeProjectKpiRepository->findBy(['isActive' => true], ['position' => 'ASC', 'id' => 'ASC']);
        $flashInfo = $contentItemRepository->findOneBy([], ['id' => 'DESC']);

        return $this->render('index.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'projets' => $projets,
            'metiers' => $metiers,
            'homeCards' => $homeCards,
            'homePractices' => $homePractices,
            'homePracticesSection' => $homePracticesSection,
            'homeHeroSection' => $homeHeroSection,
            'homeProjectsSection' => $homeProjectsSection,
            'homeAwardsSection' => $homeAwardsSection,
            'homeAwards' => $homeAwards,
            'homeProjectKpis' => $homeProjectKpis,
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
        \App\Repository\SitePageRepository $sitePageRepository
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $page = $sitePageRepository->findOneBy(['slug' => 'apropos']);
        return $this->render('about.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $page,
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

    #[Route('/amoa-si', name: 'amoa_si', options: ["sitemap" => true])]
    public function amoaSi(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
    ): Response
    {
        return $this->render('amoa-si.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $repopractice->findAll(),
            'services' => $reposervices->findAll(),
            'pract' => '',
        ]);
    }

   

    #[Route('/a-propos/metiers', name: 'metiers', options: ["sitemap" => true])]
    public function metiers(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        \App\Repository\SitePageRepository $sitePageRepository
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $page = $sitePageRepository->findOneBy(['slug' => 'metiers']);
        return $this->render('metiers.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $page,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/team', name: 'team', options: ["sitemap" => true])]
    public function team(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        TeamRepository $repoteam,
        \App\Repository\SitePageRepository $sitePageRepository
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $team = $repoteam->findAll();
        $page = $sitePageRepository->findOneBy(['slug' => 'team']);
        return $this->render('team.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'team' => $team,
            'page' => $page,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/client', name: 'client', options: ["sitemap" => true])]
    public function client(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        \App\Repository\SitePageRepository $sitePageRepository
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        $page = $sitePageRepository->findOneBy(['slug' => 'client']);
        return $this->render('client.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'page' => $page,
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

        $practices = $practiceRepository->findAll();
        $services = $servicesRepository->findAll();

        if (empty($service->getIntroductionShort())) {
            return $this->redirectToRoute('index');
        }

        return $this->render('services.html.twig', [
            'controller_name' => 'PracticeController',
            'service' => $service,
            'pract' => $practice,
            'practices' => $practices,
            'services' => $services,
        ]);
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
            'pract' => $practice->getSlug(),
            'practices' => $practices,
            'services' => $services,
            'teams' => $teams,
            'projects' => $projects,
        ]);
    }

    private function isAmoaAlias(string $slug): bool
    {
        return in_array($slug, ['consulting', 'amoa-si'], true);
    }





   
}
