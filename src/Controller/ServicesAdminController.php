<?php

namespace App\Controller;

use App\Entity\Services;
use App\Dto\StructuredContentAdminFormData;
use App\Form\ServicesType;
use App\Repository\ServicesRepository;
use App\Service\StructuredContentTranslationSynchronizer;
use App\Service\UploadManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/services')]
class ServicesAdminController extends AbstractController
{
    #[Route('', name: 'admin_services_index', methods: ['GET'])]
    #[Route('/', name: 'admin_services_index_slash', methods: ['GET'])]
    public function index(ServicesRepository $repository): Response
    {
        return $this->render('admin/services/index.html.twig', [
            'services' => $repository->findBy([], ['id' => 'DESC']),
            'practices' => [],
        ]);
    }

    #[Route('/new', name: 'admin_services_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
    {
        $service = new Services();
        $formData = new StructuredContentAdminFormData();
        $form = $this->createForm(ServicesType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $image1File = $form->get('image1File')->getData();
            $image2File = $form->get('image2File')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            if (!$formData->getImage1() && !$image1File) {
                $form->addError(new FormError("L'image principale est obligatoire."));
            }

            if ($form->isValid()) {
                if ($image1File) {
                    try {
                        $formData->setImage1($uploadManager->upload($image1File, 'services/images'));
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image principale.");
                    }
                }

                if ($image2File) {
                    try {
                        $formData->setImage2($uploadManager->upload($image2File, 'services/images'));
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image secondaire.");
                    }
                }

                if ($imageHeroFile) {
                    try {
                        $formData->setImageHero($uploadManager->upload($imageHeroFile, 'services/hero'));
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                    }
                }

                $service->setDesignation((string) $formData->getDesignation());
                $service->setImage1((string) $formData->getImage1());
                $entityManager->persist($service);
                $entityManager->flush();
                $translationSynchronizer->saveServiceFromForm($service, $formData);
                $entityManager->flush();

                $this->addFlash('success', 'Service créé.');

                return $this->redirectToRoute('admin_services_index');
            }
        }

        return $this->render('admin/services/new.html.twig', [
            'form' => $form,
            'service' => $service,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_services_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, ServicesRepository $repository, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, StructuredContentTranslationSynchronizer $translationSynchronizer): Response
    {
        $service = $repository->find($id);
        if (!$service) {
            $this->addFlash('danger', 'Service introuvable.');
            return $this->redirectToRoute('admin_services_index');
        }

        $originalImage1 = $service->getImage1();
        $originalImage2 = $service->getImage2();
        $originalImageHero = $service->getImageHero();

        $formData = $translationSynchronizer->serviceFormData($service);
        $form = $this->createForm(ServicesType::class, $formData, ['data_class' => StructuredContentAdminFormData::class]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $image1File = $form->get('image1File')->getData();
            $image2File = $form->get('image2File')->getData();
            $imageHeroFile = $form->get('imageHeroFile')->getData();

            $currentImage1 = $formData->getImage1();
            if (($currentImage1 === null || $currentImage1 === '') && !$image1File) {
                $formData->setImage1($originalImage1);
            }

            if ($form->isValid()) {
                if ($image1File) {
                    try {
                        $formData->setImage1($uploadManager->upload($image1File, 'services/images'));
                        $uploadManager->remove($originalImage1);
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image principale.");
                    }
                }

                if ($image2File) {
                    try {
                        $formData->setImage2($uploadManager->upload($image2File, 'services/images'));
                        $uploadManager->remove($originalImage2);
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image secondaire.");
                    }
                }

                if ($imageHeroFile) {
                    try {
                        $formData->setImageHero($uploadManager->upload($imageHeroFile, 'services/hero'));
                        $uploadManager->remove($originalImageHero);
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                    }
                }

                $translationSynchronizer->saveServiceFromForm($service, $formData);
                $entityManager->flush();

                $this->addFlash('success', 'Service mis à jour.');

                return $this->redirectToRoute('admin_services_index');
            }
        }

        return $this->render('admin/services/edit.html.twig', [
            'form' => $form,
            'service' => $service,
            'practices' => [],
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_services_delete', methods: ['POST'])]
    public function delete(int $id, ServicesRepository $repository, Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager): Response
    {
        $service = $repository->find($id);
        if (!$service) {
            $this->addFlash('danger', 'Service introuvable.');
            return $this->redirectToRoute('admin_services_index');
        }

        if ($this->isCsrfTokenValid('delete'.$service->getId(), $request->request->get('_token'))) {
            $uploadManager->remove($service->getImage1());
            $uploadManager->remove($service->getImage2());
            $uploadManager->remove($service->getImageHero());
            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Service supprimé.');
        }

        return $this->redirectToRoute('admin_services_index');
    }
}
