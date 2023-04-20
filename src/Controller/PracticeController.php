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
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository; // ajout de l'importation
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class PracticeController extends AbstractController
{
    
    #[Route('/', name: 'index')]
    public function index(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        Request $request
        ): Response
    {
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();

        return $this->render('index.html.twig', [
            'controller_name' => 'PracticeController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }
    
    #[Route('/{id}/{slug}', name: 'practice')]
    public function practices(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices, 
        $id,
        $slug
        ): Response
    {
        $practice = $repopractice->find($id);
        $pract = $slug;
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        return $this->render('practices.html.twig', [
            'controller_name' => 'PracticeController',
            'practice' => $practice,
            'practices' => $practices,
            'pract' => $pract,
            'services' => $services,
        ]);
    }

    #[Route('/{practice}/{id}/{slug}', name: 'service')]
    public function services(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices,
        $id,
        $practice
    ): Response {
        $service = $reposervices->find($id);
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
        // Vérifier que la colonne IntroductionShort n'est pas vide
        
        if (empty($service->getIntroductionShort())) {
            return $this->redirectToRoute('index');
        }
        $pract = $practice;

        return $this->render('services.html.twig', [
            'controller_name' => 'PracticeController',
            'service' => $service,
            'pract' => $pract,
            'practices' => $practices,
            'services' => $services,
        ]);
    }


    #[Route('/discloser', name: 'discloser')]
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

    #[Route('/a_propos', name: 'apropos')]
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

    #[Route('/contact', name: 'contact')]
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

   

    #[Route('/metiers', name: 'metiers')]
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

    #[Route('/team', name: 'team')]
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

    #[Route('/client', name: 'client')]
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

    #[Route('/rse', name: 'rse')]
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
    #[Route('/politiquergpd', name: 'polrgpd')]
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


   
}
