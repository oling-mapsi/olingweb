<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PracticeController extends AbstractController
{
    
    #[Route('/', name: 'index')]
    public function index(): Response
    {
        return $this->render('index.html.twig', [
            'controller_name' => 'PracticeController',
        ]);
    }
    
    #[Route('/practices', name: 'practices')]
    public function practices(): Response
    {
        return $this->render('practices.html.twig', [
            'controller_name' => 'PracticeController',
        ]);
    }

    #[Route('/discloser', name: 'discloser')]
    public function discloser(): Response
    {
        return $this->render('page-terms.html.twig', [
            'controller_name' => 'PracticeController',
        ]);
    }

    #[Route('/contact', name: 'contact')]
    public function contact(): Response
    {
        return $this->render('contact.html.twig', [
            'controller_name' => 'PracticeController',
        ]);
    }

    #[Route('/services', name: 'services')]
    public function services(): Response
    {
        return $this->render('services.html.twig', [
            'controller_name' => 'PracticeController',
        ]);
    }
}
