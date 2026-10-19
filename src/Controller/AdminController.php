<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_index', methods: ['GET'])]
    public function index(\App\Service\CmsBusinessNavigation $business, \App\Service\CmsSiteNavigation $site): Response
    {
        return $this->render('admin/index.html.twig', [
            'practices' => [],'siteSections'=>$site->sections(),
            'families' => $business->families(),
            'decisionCount' => count($business->decisions()),
        ]);
    }
}
