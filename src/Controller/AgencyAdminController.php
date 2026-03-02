<?php

namespace App\Controller;

use App\Entity\Agency;
use App\Form\AgencyType;
use App\Repository\AgencyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/agences')]
class AgencyAdminController extends AbstractController
{
    #[Route('', name: 'admin_agencies_index', methods: ['GET'])]
    #[Route('/', name: 'admin_agencies_index_slash', methods: ['GET'])]
    public function index(AgencyRepository $repository): Response
    {
        return $this->render('admin/agencies/index.html.twig', [
            'agencies' => $repository->findAllOrdered(),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_agencies_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $agency = new Agency();
        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($agency);
            $entityManager->flush();

            $this->addFlash('success', 'Agence créée.');

            return $this->redirectToRoute('admin_agencies_index');
        }

        return $this->render('admin/agencies/new.html.twig', [
            'form' => $form,
            'agency' => $agency,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_agencies_edit', methods: ['GET', 'POST'])]
    public function edit(Agency $agency, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AgencyType::class, $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Agence mise à jour.');

            return $this->redirectToRoute('admin_agencies_index');
        }

        return $this->render('admin/agencies/edit.html.twig', [
            'form' => $form,
            'agency' => $agency,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_agencies_delete', methods: ['POST'])]
    public function delete(Request $request, Agency $agency, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$agency->getId(), $request->request->get('_token'))) {
            $entityManager->remove($agency);
            $entityManager->flush();
            $this->addFlash('success', 'Agence supprimée.');
        }

        return $this->redirectToRoute('admin_agencies_index');
    }
}
