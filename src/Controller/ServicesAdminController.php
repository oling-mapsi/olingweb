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
    public function index(Request $request, ServicesRepository $repository): Response
    {
        return $this->forward(CmsCatalogueAdminController::class.'::index', ['type' => 'service'], $request->query->all());
    }

    #[Route('/new', name: 'admin_services_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, UploadManager $uploadManager, StructuredContentTranslationSynchronizer $translationSynchronizer, \Symfony\Component\String\Slugger\SluggerInterface $slugger): Response
    {
        $service = new Services();
        $formData = new StructuredContentAdminFormData();
        if ($request->query->getInt('practice') > 0) {
            $formData->setPractice($entityManager->find(\App\Entity\Practice::class, $request->query->getInt('practice')));
        }
        $form = $this->createForm(ServicesType::class, $formData, ['data_class' => StructuredContentAdminFormData::class, 'require_practice' => true]);
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
                $baseSlug = (string) $slugger->slug((string) $formData->getDesignation())->lower();
                $baseSlug = $baseSlug !== '' ? substr($baseSlug, 0, 240) : 'service';
                $newSlug = $baseSlug; $suffix = 2;
                while ($entityManager->getRepository(Services::class)->findOneBy(['slug' => $newSlug])) { $newSlug = $baseSlug.'-'.$suffix++; }
                $service->initializeSlug($newSlug);
                $service->setImage1((string) $formData->getImage1());
                $entityManager->persist($service);
                $entityManager->flush();
                if ($form->get('imageHeroUseMain')->getData() && !$imageHeroFile) {
                    $formData->setImageHero(null);
                }

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
        $form = $this->createForm(ServicesType::class, $formData, ['data_class' => StructuredContentAdminFormData::class, 'lock_route_practice' => true, 'require_practice' => !\App\Service\LegacyMapsiPublicUsage::isPreservedUsage($service)]);
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
                        // Retain the old file for other pages and rollback.
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image principale.");
                    }
                }

                if ($image2File) {
                    try {
                        $formData->setImage2($uploadManager->upload($image2File, 'services/images'));
                        // Retain the old file for other pages and rollback.
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image secondaire.");
                    }
                }

                if ($imageHeroFile) {
                    try {
                        $formData->setImageHero($uploadManager->upload($imageHeroFile, 'services/hero'));
                        // Retain the old file for other pages and rollback.
                    } catch (FileException $exception) {
                        $this->addFlash('danger', "Impossible d'envoyer l'image hero.");
                    }
                }

                if ($form->get('imageHeroUseMain')->getData() && !$imageHeroFile) {
                    $formData->setImageHero(null);
                }

                $translationSynchronizer->saveServiceFromForm($service, $formData);
                $entityManager->flush();

                $this->addFlash('success', 'Service historique #'.$service->getId().' mis à jour : /'.$service->getPractice()?->getSlug().'/'.$service->getSlug());

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



            $entityManager->remove($service);
            $entityManager->flush();
            $this->addFlash('success', 'Service supprimé.');
        }

        return $this->redirectToRoute('admin_services_index');
    }
}
