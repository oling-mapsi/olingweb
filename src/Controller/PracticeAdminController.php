<?php

namespace App\Controller;

use App\Entity\Practice;
use App\Form\PracticeType;
use App\Repository\PracticeRepository;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/practices')]
class PracticeAdminController extends AbstractController
{
    #[Route('/', name: 'admin_practices_index', methods: ['GET'])]
    public function index(PracticeRepository $repository): Response
    {
        $practiceItems = $repository->findBy([], ['id' => 'DESC']);
        $featuredItems = array_values(array_filter($practiceItems, static function ($practice) {
            return $practice->isFeaturedHome();
        }));
        usort($featuredItems, static function ($a, $b) {
            $rankA = $a->getFeaturedHomeRank() ?? 9999;
            $rankB = $b->getFeaturedHomeRank() ?? 9999;
            if ($rankA === $rankB) {
                return ($b->getId() ?? 0) <=> ($a->getId() ?? 0);
            }
            return $rankA <=> $rankB;
        });

        return $this->render('admin/practices/index.html.twig', [
            'practiceItems' => $practiceItems,
            'featuredItems' => $featuredItems,
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_practices_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $practice = new Practice();
        $form = $this->createForm(PracticeType::class, $practice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image1File = $form->get('image1File')->getData();
            $image2File = $form->get('image2File')->getData();

            if ($image1File) {
                try {
                    $practice->setImage1($uploadManager->upload($image1File, 'practices/images'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image 1.");
                }
            }

            if ($image2File) {
                try {
                    $practice->setImage2($uploadManager->upload($image2File, 'practices/images'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image 2.");
                }
            }

            $entityManager->persist($practice);
            $entityManager->flush();

            $this->addFlash('success', 'Practice créée.');

            return $this->redirectToRoute('admin_practices_index');
        }

        return $this->render('admin/practices/new.html.twig', [
            'form' => $form,
            'practice' => $practice,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_practices_edit', methods: ['GET', 'POST'])]
    public function edit(Practice $practice, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $originalImage1 = $practice->getImage1();
        $originalImage2 = $practice->getImage2();

        $form = $this->createForm(PracticeType::class, $practice);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image1File = $form->get('image1File')->getData();
            $image2File = $form->get('image2File')->getData();

            if ($image1File) {
                try {
                    $practice->setImage1($uploadManager->upload($image1File, 'practices/images'));
                    $uploadManager->remove($originalImage1);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image 1.");
                }
            }

            if ($image2File) {
                try {
                    $practice->setImage2($uploadManager->upload($image2File, 'practices/images'));
                    $uploadManager->remove($originalImage2);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image 2.");
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Practice mise à jour.');

            return $this->redirectToRoute('admin_practices_index');
        }

        return $this->render('admin/practices/edit.html.twig', [
            'form' => $form,
            'practice' => $practice,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_practices_delete', methods: ['POST'])]
    public function delete(Request $request, Practice $practice, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$practice->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($practice->getImage1());
            $uploadManager->remove($practice->getImage2());
            $entityManager->remove($practice);
            $entityManager->flush();
            $this->addFlash('success', 'Practice supprimée.');
        }

        return $this->redirectToRoute('admin_practices_index');
    }

    #[Route('/home-order', name: 'admin_practices_home_order', methods: ['POST'])]
    public function homeOrder(Request $request, PracticeRepository $repository, EntityManagerInterface $entityManager): JsonResponse
    {
        $payload = json_decode($request->getContent() ?? '', true) ?? [];
        if (!$this->isCsrfTokenValid('home_order', $payload['_token'] ?? '')) {
            return new JsonResponse(['success' => false, 'message' => 'CSRF invalide'], 400);
        }

        $ids = $payload['ids'] ?? [];
        if (!is_array($ids)) {
            return new JsonResponse(['success' => false, 'message' => 'Format invalide'], 400);
        }

        $position = 1;
        foreach ($ids as $id) {
            $practice = $repository->find($id);
            if (!$practice || !$practice->isFeaturedHome()) {
                continue;
            }
            $practice->setFeaturedHomeRank($position);
            $position++;
        }

        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }
}
