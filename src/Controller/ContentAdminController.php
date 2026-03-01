<?php

namespace App\Controller;

use App\Entity\ContentItem;
use App\Form\ContentItemType;
use App\Repository\ContentItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Service\UploadManager;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/contenus')]
class ContentAdminController extends AbstractController
{
    #[Route('/', name: 'admin_content_index', methods: ['GET'])]
    public function index(ContentItemRepository $repository): Response
    {
        return $this->render('admin/content/index.html.twig', [
            'contents' => $repository->findBy([], ['id' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_content_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $contentItem = new ContentItem();
        $form = $this->createForm(ContentItemType::class, $contentItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();
            $iconFile = $form->get('iconFile')->getData();

            if ($imageFile) {
                try {
                    $contentItem->setImagePath($uploadManager->upload($imageFile, 'content/images'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $contentItem->setImageHero($uploadManager->upload($imageHeroFile, 'content/hero'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image recadrée.");
                }
            }

            if ($iconFile) {
                try {
                    $contentItem->setIconPath($uploadManager->upload($iconFile, 'content/icons'));
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'icône.");
                }
            }

            $entityManager->persist($contentItem);
            $entityManager->flush();

            $this->addFlash('success', 'Contenu créé.');

            return $this->redirectToRoute('admin_content_index');
        }

        return $this->render('admin/content/new.html.twig', [
            'form' => $form,
            'contentItem' => $contentItem,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_content_edit', methods: ['GET', 'POST'])]
    public function edit(ContentItem $contentItem, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $form = $this->createForm(ContentItemType::class, $contentItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();
            $iconFile = $form->get('iconFile')->getData();

            if ($imageFile) {
                try {
                    $oldPath = $contentItem->getImagePath();
                    $contentItem->setImagePath($uploadManager->upload($imageFile, 'content/images'));
                    $uploadManager->remove($oldPath);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image.");
                }
            }

            if ($imageHeroFile) {
                try {
                    $oldPath = $contentItem->getImageHero();
                    $contentItem->setImageHero($uploadManager->upload($imageHeroFile, 'content/hero'));
                    $uploadManager->remove($oldPath);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'image recadrée.");
                }
            }

            if ($iconFile) {
                try {
                    $oldPath = $contentItem->getIconPath();
                    $contentItem->setIconPath($uploadManager->upload($iconFile, 'content/icons'));
                    $uploadManager->remove($oldPath);
                } catch (FileException $exception) {
                    $this->addFlash('danger', "Impossible d'envoyer l'icône.");
                }
            }

            $entityManager->flush();

            $this->addFlash('success', 'Contenu mis à jour.');

            return $this->redirectToRoute('admin_content_index');
        }

        return $this->render('admin/content/edit.html.twig', [
            'form' => $form,
            'contentItem' => $contentItem,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_content_delete', methods: ['POST'])]
    public function delete(Request $request, ContentItem $contentItem, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$contentItem->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($contentItem->getImagePath());
            $uploadManager->remove($contentItem->getImageHero());
            $uploadManager->remove($contentItem->getIconPath());
            $entityManager->remove($contentItem);
            $entityManager->flush();
            $this->addFlash('success', 'Contenu supprimé.');
        }

        return $this->redirectToRoute('admin_content_index');
    }

}
