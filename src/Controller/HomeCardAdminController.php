<?php

namespace App\Controller;

use App\Entity\HomeCard;
use App\Form\HomeCardType;
use App\Repository\HomeCardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/mise-en-avant')]
class HomeCardAdminController extends AbstractController
{
    #[Route('', name: 'admin_home_cards_index', methods: ['GET'])]
    #[Route('/', name: 'admin_home_cards_index_slash', methods: ['GET'])]
    public function index(HomeCardRepository $repository): Response
    {
        return $this->render('admin/home_cards/index.html.twig', [
            'homeCards' => $repository->findBy([], ['createdAt' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_home_cards_new', methods: ['GET', 'POST'])]
    #[Route('/new/', name: 'admin_home_cards_new_slash', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $homeCard = new HomeCard();
        $form = $this->createForm(HomeCardType::class, $homeCard);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $selected = array_filter([
                $homeCard->getService(),
                $homeCard->getProjet(),
                $homeCard->getMetier(),
            ]);

            if (count($selected) !== 1) {
                $form->addError(new FormError('Sélectionnez exactement une entité à mettre en avant.'));
            }

            if ($form->isValid()) {
                $entityManager->persist($homeCard);
                $entityManager->flush();

                $this->addFlash('success', 'Mise en avant créée.');

                return $this->redirectToRoute('admin_home_cards_index');
            }
        }

        return $this->render('admin/home_cards/new.html.twig', [
            'form' => $form,
            'homeCard' => $homeCard,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_home_cards_edit', methods: ['GET', 'POST'])]
    public function edit(HomeCard $homeCard, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(HomeCardType::class, $homeCard);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $selected = array_filter([
                $homeCard->getService(),
                $homeCard->getProjet(),
                $homeCard->getMetier(),
            ]);

            if (count($selected) !== 1) {
                $form->addError(new FormError('Sélectionnez exactement une entité à mettre en avant.'));
            }

            if ($form->isValid()) {
                $entityManager->flush();

                $this->addFlash('success', 'Mise en avant mise à jour.');

                return $this->redirectToRoute('admin_home_cards_index');
            }
        }

        return $this->render('admin/home_cards/edit.html.twig', [
            'form' => $form,
            'homeCard' => $homeCard,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_home_cards_delete', methods: ['POST'])]
    public function delete(Request $request, HomeCard $homeCard, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$homeCard->getId(), $request->request->get('_token'))) {
            $entityManager->remove($homeCard);
            $entityManager->flush();
            $this->addFlash('success', 'Mise en avant supprimée.');
        }

        return $this->redirectToRoute('admin_home_cards_index');
    }
}
