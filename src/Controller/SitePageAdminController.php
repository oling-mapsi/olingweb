<?php

namespace App\Controller;

use App\Entity\SitePage;
use App\Form\SitePageType;
use App\Repository\SitePageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/pages')]
class SitePageAdminController extends AbstractController
{
    #[Route('', name: 'admin_pages_index', methods: ['GET'])]
    #[Route('/', name: 'admin_pages_index_slash', methods: ['GET'])]
    public function index(SitePageRepository $repository): Response
    {
        return $this->render('admin/pages/index.html.twig', [
            'pages' => $repository->findBy([], ['slug' => 'ASC']),
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_pages_edit', methods: ['GET', 'POST'])]
    public function edit(SitePage $page, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SitePageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Page mise à jour.');
            return $this->redirectToRoute('admin_pages_index');
        }

        return $this->render('admin/pages/edit.html.twig', [
            'form' => $form,
            'page' => $page,
            'practices' => [],
        ]);
    }
}
