<?php

namespace App\Controller;

use App\Entity\HomeAwardItem;
use App\Form\HomeAwardItemType;
use App\Repository\HomeAwardItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/home/awards-items')]
class HomeAwardItemAdminController extends AbstractController
{
    #[Route('', name: 'admin_home_awards_items_index', methods: ['GET'])]
    #[Route('/', name: 'admin_home_awards_items_index_slash', methods: ['GET'])]
    public function index(HomeAwardItemRepository $repository): Response
    {
        return $this->render('admin/home_awards/index.html.twig', [
            'items' => $repository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_home_awards_items_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, HomeAwardItemRepository $repository): Response
    {
        $item = new HomeAwardItem();
        $form = $this->createForm(HomeAwardItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($item->getPosition() === null) {
                $count = $repository->count([]);
                $item->setPosition($count + 1);
            }
            $entityManager->persist($item);
            $entityManager->flush();
            $this->addFlash('success', 'Item ajouté.');
            return $this->redirectToRoute('admin_home_awards_items_index');
        }

        return $this->render('admin/home_awards/new.html.twig', [
            'form' => $form,
            'item' => $item,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_home_awards_items_edit', methods: ['GET', 'POST'])]
    public function edit(HomeAwardItem $item, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HomeAwardItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Item mis à jour.');
            return $this->redirectToRoute('admin_home_awards_items_index');
        }

        return $this->render('admin/home_awards/edit.html.twig', [
            'form' => $form,
            'item' => $item,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_home_awards_items_delete', methods: ['POST'])]
    public function delete(Request $request, HomeAwardItem $item, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$item->getId(), $request->request->get('_token'))) {
            $entityManager->remove($item);
            $entityManager->flush();
            $this->addFlash('success', 'Item supprimé.');
        }

        return $this->redirectToRoute('admin_home_awards_items_index');
    }
}
