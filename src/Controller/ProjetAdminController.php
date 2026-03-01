<?php

namespace App\Controller;

use App\Entity\Projet;
use App\Form\ProjetType;
use App\Repository\ProjetRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/projets')]
class ProjetAdminController extends AbstractController
{
    #[Route('', name: 'admin_projets_index', methods: ['GET'])]
    #[Route('/', name: 'admin_projets_index_slash', methods: ['GET'])]
    public function index(ProjetRepository $repository): Response
    {
        $projectItems = $repository->findBy([], ['id' => 'DESC']);
        $featuredItems = array_values(array_filter($projectItems, static function ($projet) {
            return $projet->isFeaturedProjects();
        }));
        usort($featuredItems, static function ($a, $b) {
            $rankA = $a->getFeaturedProjectsRank() ?? 9999;
            $rankB = $b->getFeaturedProjectsRank() ?? 9999;
            if ($rankA === $rankB) {
                return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
            }
            return $rankA <=> $rankB;
        });

        return $this->render('admin/projets/index.html.twig', [
            'projets' => $projectItems,
            'featuredItems' => $featuredItems,
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_projets_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, ProjetRepository $repository): Response
    {
        $projet = new Projet();
        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            if ($imageFile) {
                try {
                    $projet->setImage($uploadManager->upload($imageFile, 'projets/images'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $projet->setImageHero($uploadManager->upload($imageHeroFile, 'projets/hero'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                }
            }

            if ($projet->isFeaturedProjects()) {
                $featuredCount = $repository->count(['featuredProjects' => true]);
                if ($featuredCount >= 6) {
                    $projet->setFeaturedProjects(false);
                    $this->addFlash('warning', 'Maximum 6 projets peuvent être mis en avant. Désélectionne-en un avant.');
                } elseif ($projet->getFeaturedProjectsRank() === null) {
                    $projet->setFeaturedProjectsRank($featuredCount + 1);
                }
            } else {
                $projet->setFeaturedProjectsRank(null);
            }

            $entityManager->persist($projet);
            $entityManager->flush();

            $this->addFlash('success', 'Projet créé.');

            return $this->redirectToRoute('admin_projets_index');
        }

        return $this->render('admin/projets/new.html.twig', [
            'form' => $form,
            'projet' => $projet,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_projets_edit', methods: ['GET', 'POST'])]
    public function edit(Projet $projet, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, ProjetRepository $repository): Response
    {
        $originalImage = $projet->getImage();
        $originalImageHero = $projet->getImageHero();
        $wasFeatured = $projet->isFeaturedProjects();

        $form = $this->createForm(ProjetType::class, $projet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            if ($imageFile) {
                try {
                    $projet->setImage($uploadManager->upload($imageFile, 'projets/images'));
                    $uploadManager->remove($originalImage);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $projet->setImageHero($uploadManager->upload($imageHeroFile, 'projets/hero'));
                    $uploadManager->remove($originalImageHero);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                }
            }

            if ($projet->isFeaturedProjects() && !$wasFeatured) {
                $featuredCount = $repository->count(['featuredProjects' => true]);
                if ($featuredCount >= 6) {
                    $projet->setFeaturedProjects(false);
                    $this->addFlash('warning', 'Maximum 6 projets peuvent être mis en avant. Désélectionne-en un avant.');
                } elseif ($projet->getFeaturedProjectsRank() === null) {
                    $projet->setFeaturedProjectsRank($featuredCount + 1);
                }
            } elseif (!$projet->isFeaturedProjects()) {
                $projet->setFeaturedProjectsRank(null);
            }

            $entityManager->flush();

            $this->addFlash('success', 'Projet mis à jour.');

            return $this->redirectToRoute('admin_projets_index');
        }

        return $this->render('admin/projets/edit.html.twig', [
            'form' => $form,
            'projet' => $projet,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_projets_delete', methods: ['POST'])]
    public function delete(Request $request, Projet $projet, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$projet->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($projet->getImage());
            $uploadManager->remove($projet->getImageHero());
            $entityManager->remove($projet);
            $entityManager->flush();
            $this->addFlash('success', 'Projet supprimé.');
        }

        return $this->redirectToRoute('admin_projets_index');
    }

    #[Route('/featured-order', name: 'admin_projets_featured_order', methods: ['POST'])]
    public function featuredOrder(Request $request, ProjetRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = json_decode($request->getContent() ?? '', true) ?? [];
        if (!$this->isCsrfTokenValid('projets_featured_order', $payload['_token'] ?? '')) {
            return new JsonResponse(['success' => false, 'message' => 'CSRF invalide'], 400);
        }

        $ids = $payload['ids'] ?? [];
        if (!is_array($ids)) {
            return new JsonResponse(['success' => false, 'message' => 'Format invalide'], 400);
        }

        $position = 1;
        foreach ($ids as $id) {
            $projet = $repository->find($id);
            if (!$projet || !$projet->isFeaturedProjects()) {
                continue;
            }
            $projet->setFeaturedProjectsRank($position);
            $position++;
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
