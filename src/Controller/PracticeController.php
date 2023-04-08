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
    
    #[Route('/practice', name: 'app_practice')]
    public function practice(): Response
    {
        return $this->render('practice/index.html.twig', [
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
}
