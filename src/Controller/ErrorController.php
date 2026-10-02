<?php

namespace App\Controller;

use App\Repository\PracticeRepository;
use App\Repository\ServicesRepository;
use App\Service\LocalizedContentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Middleware\XRobotsTagMiddleware;


class ErrorController extends AbstractController
{
    public function __construct(private readonly LocalizedContentResolver $localizedContentResolver)
    {
    }

    /**
     * @Route("/error", name="app_error")
     */
    public function index(PracticeRepository $repopractice, ServicesRepository $reposervices): Response
    {
        $practices = array_map(fn ($practice) => $this->localizedContentResolver->getFrenchPracticeView($practice), $repopractice->findAll());
        $services = array_map(fn ($service) => $this->localizedContentResolver->getFrenchServiceView($service), $reposervices->findAll());
        return $this->render('error/index.html.twig', [
            'controller_name' => 'ErrorController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }

    /**
     * @Route("/error404", name="app_error404")
     */
    public function error404(PracticeRepository $repopractice, ServicesRepository $reposervices): Response
    {
        $practices = array_map(fn ($practice) => $this->localizedContentResolver->getFrenchPracticeView($practice), $repopractice->findAll());
        $services = array_map(fn ($service) => $this->localizedContentResolver->getFrenchServiceView($service), $reposervices->findAll());
        return $this->render('error/error404.html.twig', [
            'controller_name' => 'ErrorController',
            'practices' => $practices,
            'services' => $services,
            'pract' => '',
        ]);
    }
}
