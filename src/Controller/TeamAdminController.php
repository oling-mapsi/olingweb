<?php

namespace App\Controller;

use App\Entity\Team;
use App\Form\TeamType;
use App\Repository\TeamRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/teams')]
class TeamAdminController extends AbstractController
{
    #[Route('', name: 'admin_teams_index', methods: ['GET'])]
    #[Route('/', name: 'admin_teams_index_slash', methods: ['GET'])]
    public function index(TeamRepository $repository): Response
    {
        return $this->render('admin/teams/index.html.twig', [
            'teams' => $repository->findBy([], ['id' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_teams_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                try {
                    $team->setPhoto($uploadManager->upload($photoFile, 'teams/photos'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer la photo.");
                }
            }

            $entityManager->persist($team);
            $entityManager->flush();

            $this->addFlash('success', 'Membre créé.');

            return $this->redirectToRoute('admin_teams_index');
        }

        return $this->render('admin/teams/new.html.twig', [
            'form' => $form,
            'team' => $team,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_teams_edit', methods: ['GET', 'POST'])]
    public function edit(Team $team, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $originalPhoto = $team->getPhoto();

        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photoFile')->getData();

            if ($photoFile) {
                try {
                    $team->setPhoto($uploadManager->upload($photoFile, 'teams/photos'));
                    $uploadManager->remove($originalPhoto);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer la photo.");
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Membre mis à jour.');

            return $this->redirectToRoute('admin_teams_index');
        }

        return $this->render('admin/teams/edit.html.twig', [
            'form' => $form,
            'team' => $team,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_teams_delete', methods: ['POST'])]
    public function delete(Request $request, Team $team, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$team->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($team->getPhoto());
            $entityManager->remove($team);
            $entityManager->flush();
            $this->addFlash('success', 'Membre supprimé.');
        }

        return $this->redirectToRoute('admin_teams_index');
    }
}
