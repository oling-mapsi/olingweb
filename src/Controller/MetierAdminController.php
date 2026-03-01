<?php

namespace App\Controller;

use App\Entity\Metier;
use App\Form\MetierType;
use App\Repository\MetierRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/metiers')]
class MetierAdminController extends AbstractController
{
    #[Route('/', name: 'admin_metiers_index', methods: ['GET'])]
    public function index(MetierRepository $repository): Response
    {
        return $this->render('admin/metiers/index.html.twig', [
            'metiers' => $repository->findBy([], ['id' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_metiers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $metier = new Metier();
        $form = $this->createForm(MetierType::class, $metier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            if ($imageFile) {
                try {
                    $metier->setImage($uploadManager->upload($imageFile, 'metiers/images'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $metier->setImageHero($uploadManager->upload($imageHeroFile, 'metiers/hero'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                }
            }

            $entityManager->persist($metier);
            $entityManager->flush();

            $this->addFlash('success', 'Métier créé.');

            return $this->redirectToRoute('admin_metiers_index');
        }

        return $this->render('admin/metiers/new.html.twig', [
            'form' => $form,
            'metier' => $metier,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_metiers_edit', methods: ['GET', 'POST'])]
    public function edit(Metier $metier, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $originalImage = $metier->getImage();
        $originalImageHero = $metier->getImageHero();

        $form = $this->createForm(MetierType::class, $metier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            if ($imageFile) {
                try {
                    $metier->setImage($uploadManager->upload($imageFile, 'metiers/images'));
                    $uploadManager->remove($originalImage);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $metier->setImageHero($uploadManager->upload($imageHeroFile, 'metiers/hero'));
                    $uploadManager->remove($originalImageHero);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Métier mis à jour.');

            return $this->redirectToRoute('admin_metiers_index');
        }

        return $this->render('admin/metiers/edit.html.twig', [
            'form' => $form,
            'metier' => $metier,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_metiers_delete', methods: ['POST'])]
    public function delete(Request $request, Metier $metier, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$metier->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($metier->getImage());
            $uploadManager->remove($metier->getImageHero());
            $entityManager->remove($metier);
            $entityManager->flush();
            $this->addFlash('success', 'Métier supprimé.');
        }

        return $this->redirectToRoute('admin_metiers_index');
    }
}
