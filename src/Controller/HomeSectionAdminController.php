<?php

namespace App\Controller;

use App\Entity\HomeSection;
use App\Form\HomeHeroType;
use App\Form\HomeAwardsSectionType;
use App\Form\HomeSectionType;
use App\Repository\HomeSectionRepository;
use App\Repository\HomeAwardItemRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/home')]
class HomeSectionAdminController extends AbstractController
{
    #[Route('/hero', name: 'admin_home_hero', methods: ['GET', 'POST'])]
    public function hero(HomeSectionRepository $repository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'hero']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('hero');
            $entityManager->persist($section);
        }

        $form = $this->createForm(HomeHeroType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Hero mis à jour.');
            return $this->redirectToRoute('admin_home_hero');
        }

        return $this->render('admin/home/hero.html.twig', [
            'form' => $form,
            'section' => $section,
            'practices' => [],
        ]);
    }

    #[Route('/practices', name: 'admin_home_practices', methods: ['GET', 'POST'])]
    public function practices(HomeSectionRepository $repository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'practices']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('practices');
            $entityManager->persist($section);
        }

        $form = $this->createForm(HomeSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_practices');
        }

        return $this->render('admin/home/practices.html.twig', [
            'form' => $form,
            'section' => $section,
            'practices' => [],
        ]);
    }

    #[Route('/projects', name: 'admin_home_projects', methods: ['GET', 'POST'])]
    public function projects(HomeSectionRepository $repository, \App\Repository\HomeProjectKpiRepository $kpiRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'projects']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('projects');
            $entityManager->persist($section);
        }

        $form = $this->createForm(HomeSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_projects');
        }

        return $this->render('admin/home/projects.html.twig', [
            'form' => $form,
            'section' => $section,
            'kpis' => $kpiRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'practices' => [],
        ]);
    }

    #[Route('/awards', name: 'admin_home_awards', methods: ['GET', 'POST'])]
    public function awards(HomeSectionRepository $repository, HomeAwardItemRepository $awardRepository, EntityManagerInterface $entityManager, Request $request): Response
    {
        $section = $repository->findOneBy(['slug' => 'awards']);
        if (!$section) {
            $section = new HomeSection();
            $section->setSlug('awards');
            $entityManager->persist($section);
        }

        $form = $this->createForm(HomeAwardsSectionType::class, $section);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $section->touchUpdatedAt();
            $entityManager->flush();
            $this->addFlash('success', 'Section mise à jour.');
            return $this->redirectToRoute('admin_home_awards');
        }

        return $this->render('admin/home/awards.html.twig', [
            'form' => $form,
            'section' => $section,
            'awards' => $awardRepository->findBy([], ['position' => 'ASC', 'id' => 'ASC']),
            'practices' => [],
        ]);
    }
}
