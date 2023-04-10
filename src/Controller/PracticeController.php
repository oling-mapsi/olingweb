<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

use App\Entity\Practice;
use App\Entity\Services;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;

class PracticeController extends AbstractController
{
    
    #[Route('/', name: 'index')]
    public function index(
        PracticeRepository $repopractice,
        ServicesRepository $reposervices
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
        ): Response
    {
        $service = $reposervices->find($id);
        $pract = $practice;
        $practices = $repopractice->findAll();
        $services = $reposervices->findAll();
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
}
