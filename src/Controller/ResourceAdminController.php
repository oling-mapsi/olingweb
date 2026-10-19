<?php

namespace App\Controller;

use App\Repository\SitePageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/resources')]
final class ResourceAdminController extends AbstractController
{
    #[Route('', name: 'admin_resources_index', methods: ['GET'])]
    #[Route('/', name: 'admin_resources_index_slash', methods: ['GET'])]
    public function index(Request $request, SitePageRepository $repository): Response
    {
        return $this->forward(CmsCatalogueAdminController::class.'::index', ['type' => 'resource'], $request->query->all());
    }
}
