<?php

namespace App\Controller;

use App\Entity\Email;
use App\Entity\Team;
use App\Form\EmailType;
use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Repository\EmailRepository;
use App\Repository\TeamRepository;
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
        Request $request
    ): Response {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();

        return $this->render('index.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }

    


    #[Route('/mentions-legales', name: 'discloser')]
    public function discloser(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('page-terms.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }

    #[Route('/a-propos', name: 'apropos', options: ["sitemap" => true])]
    public function apropos(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('about.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
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

   

    #[Route('/a-propos/metiers', name: 'metiers', options: ["sitemap" => true])]
    public function metiers(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('metiers.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
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
            'pract' => '',
        ]);
    }

    #[Route('/a-propos/client', name: 'client', options: ["sitemap" => true])]
    public function client(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('client.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
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
    ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('polrgpd.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
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




    #[Route('{practice}/{slug}', name: 'service')]
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



    #[Route('{slug}', name: 'practice')]
    public function practices(
        PracticeRepository $practiceRepository,
        ServicesRepository $servicesRepository,
        $slug
    ): Response {
        $practice = $practiceRepository->findOneBy(['slug' => $slug]);

        if (!$practice) {
            throw $this->createNotFoundException('La pratique n\'existe pas.');
        }

        $practices = $practiceRepository->findAll();
        $services = $servicesRepository->findAll();
     

        return $this->render('practices.html.twig', [
            'controller_name' => 'PracticeController',
            'practice' => $practice,
            'pract' => $slug,
            'practices' => $practices,
            'services' => $services,
        ]);
    }





   
}
