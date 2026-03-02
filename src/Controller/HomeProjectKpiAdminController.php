<?php

namespace App\Controller;

use App\Entity\HomeProjectKpi;
use App\Form\HomeProjectKpiType;
use App\Repository\HomeProjectKpiRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/home/projects-kpis')]
class HomeProjectKpiAdminController extends AbstractController
{
    #[Route('', name: 'admin_home_projects_kpis_index', methods: ['GET'])]
    #[Route('/', name: 'admin_home_projects_kpis_index_slash', methods: ['GET'])]
    public function index(HomeProjectKpiRepository $repository): Response
    {
        return $this->render('admin/home_projects/kpis/index.html.twig', [
            'items' => $repository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_home_projects_kpis_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = new HomeProjectKpi();
        $item->setIsActive(true);

        $form = $this->createForm(HomeProjectKpiType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'KPI ajouté.');

            return $this->redirectToRoute('admin_home_projects');
        }

        return $this->render('admin/home_projects/kpis/new.html.twig', [
            'form' => $form,
            'item' => $item,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_home_projects_kpis_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, HomeProjectKpiRepository $repository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = $repository->find($id);
        if (!$item) {
            $this->addFlash('danger', 'KPI introuvable.');
            return $this->redirectToRoute('admin_home_projects');
        }

        $form = $this->createForm(HomeProjectKpiType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'KPI mis à jour.');

            return $this->redirectToRoute('admin_home_projects');
        }

        return $this->render('admin/home_projects/kpis/edit.html.twig', [
            'form' => $form,
            'item' => $item,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_home_projects_kpis_delete', methods: ['POST'])]
    public function delete(int $id, HomeProjectKpiRepository $repository, Request $request, EntityManagerInterface $entityManager): Response
    {
        $item = $repository->find($id);
        if (!$item) {
            $this->addFlash('danger', 'KPI introuvable.');
            return $this->redirectToRoute('admin_home_projects');
        }

        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
            $this->addFlash('success', 'KPI supprimé.');
        }

        return $this->redirectToRoute('admin_home_projects');
    }
}
