<?php

namespace App\Controller;

use App\Entity\LegalPage;
use App\Form\LegalPageType;
use App\Repository\LegalPageRepository;
use App\Service\LegalPageDefaults;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/legal-pages')]
class LegalPageAdminController extends AbstractController
{
    #[Route('', name: 'admin_legal_pages', methods: ['GET'])]
    #[Route('/', name: 'admin_legal_pages_slash', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('admin_legal_pages_edit', ['slug' => 'polrgpd']);
    }

    #[Route('/{slug}', name: 'admin_legal_pages_edit', methods: ['GET', 'POST'])]
    public function edit(string $slug, Request $request, EntityManagerInterface $entityManager, LegalPageRepository $repository): Response
    {
        $defaults = LegalPageDefaults::defaults();
        if (!isset($defaults[$slug])) {
            throw $this->createNotFoundException('Page légale introuvable.');
        }

        $pages = [];
        $needsFlush = false;
        foreach ($defaults as $defaultSlug => $data) {
            $page = $repository->findOneBy(['slug' => $defaultSlug]);
            if (!$page) {
                $page = (new LegalPage())->setSlug($defaultSlug);
                $page->setTitle($data['title'] ?? null);
                $page->setBody($data['body'] ?? null);
                $page->touchUpdatedAt();
                $entityManager->persist($page);
                $needsFlush = true;
            }
            $pages[$defaultSlug] = $page;
        }

        if ($needsFlush) {
            $entityManager->flush();
        }

        $page = $pages[$slug];
        $form = $this->createForm(LegalPageType::class, $page, [
            'attr' => ['class' => 'admin-legal-form'],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $page->touchUpdatedAt();
            $entityManager->persist($page);
            $entityManager->flush();
            $this->addFlash('success', 'Page légale mise à jour.');

            return $this->redirectToRoute('admin_legal_pages_edit', ['slug' => $slug]);
        }

        return $this->render('admin/legal_pages/edit.html.twig', [
            'form' => $form->createView(),
            'slug' => $slug,
            'practices' => [],
        ]);
    }
}
