<?php

namespace App\Controller;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Enum\GrowthDestination;
use App\Form\GrowthCampaignType;
use App\Form\GrowthContentType;
use App\Repository\GrowthCampaignRepository;
use App\Repository\GrowthPublicationRepository;
use App\Service\Growth\GrowthPreviewBuilder;
use App\Service\Growth\GrowthWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/growth', name: 'admin_growth_')]
class GrowthAdminController extends AbstractController
{
    #[Route('', name: 'dashboard', methods: ['GET'])]
    public function dashboard(GrowthCampaignRepository $campaigns, GrowthPublicationRepository $publications): Response
    {
        $recentCampaigns = $campaigns->findRecent(10);
        $stats = ['campaigns' => 0, 'drafts' => 0, 'to_review' => 0, 'approved' => 0, 'errors' => 0];
        foreach ($recentCampaigns as $campaign) {
            ++$stats['campaigns'];
            $content = $campaign->getPrimaryContent();
            if ($content?->getStatus()->value === 'generated' || $content?->getStatus()->value === 'draft') {
                ++$stats['drafts'];
            }
            if ($content?->getStatus()->value === 'reviewed') {
                ++$stats['to_review'];
            }
            if ($content?->getStatus()->value === 'approved') {
                ++$stats['approved'];
            }
        }

        $recentPublications = $publications->findRecent(10);
        foreach ($recentPublications as $publication) {
            if ($publication->getStatus()->value === 'failed') {
                ++$stats['errors'];
            }
        }

        return $this->render('admin/growth/dashboard.html.twig', [
            'campaigns' => $recentCampaigns,
            'publications' => $recentPublications,
            'stats' => $stats,
            'practices' => [],
        ]);
    }

    #[Route('/campaigns', name: 'campaigns', methods: ['GET'])]
    public function campaigns(GrowthCampaignRepository $repository): Response
    {
        return $this->render('admin/growth/campaigns.html.twig', [
            'campaigns' => $repository->findRecent(),
            'practices' => [],
        ]);
    }

    #[Route('/campaigns/new', name: 'campaign_new', methods: ['GET', 'POST'])]
    public function new(Request $request, GrowthWorkflow $workflow): Response
    {
        $campaign = new GrowthCampaign();
        $form = $this->createForm(GrowthCampaignType::class, $campaign);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $workflow->createCampaign($campaign, $this->getUser());
            $this->addFlash('success', 'Campagne Growth créée.');

            return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('admin/growth/new.html.twig', ['form' => $form, 'practices' => []]);
    }

    #[Route('/campaigns/{id}', name: 'campaign_show', methods: ['GET'])]
    public function show(GrowthCampaign $campaign): Response
    {
        return $this->render('admin/growth/show.html.twig', [
            'campaign' => $campaign,
            'content' => $campaign->getPrimaryContent(),
            'destinations' => GrowthDestination::cases(),
            'practices' => [],
        ]);
    }

    #[Route('/campaigns/{id}/edit', name: 'campaign_edit', methods: ['GET', 'POST'])]
    public function edit(GrowthCampaign $campaign, Request $request, EntityManagerInterface $entityManager): Response
    {
        $content = $campaign->getPrimaryContent() ?? (new GrowthContent())->setCampaign($campaign);
        $form = $this->createForm(GrowthContentType::class, $content);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $campaign->addContent($content);
            $entityManager->persist($content);
            $entityManager->flush();
            $this->addFlash('success', 'Brouillon Growth mis à jour.');

            return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
        }

        return $this->render('admin/growth/edit.html.twig', [
            'campaign' => $campaign,
            'form' => $form,
            'practices' => [],
        ]);
    }

    #[Route('/campaigns/{id}/generate', name: 'campaign_generate', methods: ['POST'])]
    public function generate(GrowthCampaign $campaign, Request $request, GrowthWorkflow $workflow): Response
    {
        if (!$this->isCsrfTokenValid('growth_generate_'.$campaign->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
        }

        try {
            $destination = GrowthDestination::tryFrom((string) $request->request->get('destination')) ?? GrowthDestination::OLING_PUBLIC;
            $workflow->generate($campaign, $this->getUser(), $destination);
            $this->addFlash('success', 'Brouillon généré. Validation humaine requise avant publication.');
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Génération impossible: '.$exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
    }

    #[Route('/campaigns/{id}/preview/{destination}', name: 'campaign_preview', methods: ['POST'])]
    public function preview(GrowthCampaign $campaign, string $destination, Request $request, GrowthPreviewBuilder $previewBuilder): Response
    {
        if (!$this->isCsrfTokenValid('growth_preview_'.$campaign->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
        }

        $target = GrowthDestination::tryFrom($destination) ?? GrowthDestination::OLING_PUBLIC;
        try {
            $preview = $previewBuilder->build($campaign, $target);
            $this->addFlash('success', 'Preview '.$target->label().': '.$preview['url']);
        } catch (\Throwable) {
            $this->addFlash('danger', 'Preview indisponible. Le détail technique est journalisé.');
        }

        return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
    }

    #[Route('/campaigns/{id}/internal-preview', name: 'campaign_internal_preview', methods: ['GET'])]
    public function internalPreview(GrowthCampaign $campaign): Response
    {
        return $this->render('admin/growth/internal_preview.html.twig', [
            'campaign' => $campaign,
            'content' => $campaign->getPrimaryContent(),
            'practices' => [],
        ]);
    }

    #[Route('/campaigns/{id}/review', name: 'campaign_review', methods: ['POST'])]
    public function review(GrowthCampaign $campaign, Request $request, GrowthWorkflow $workflow): Response
    {
        return $this->contentAction($campaign, $request, 'growth_review_', fn (GrowthContent $content) => $workflow->review($campaign, $content, $this->getUser()));
    }

    #[Route('/campaigns/{id}/approve', name: 'campaign_approve', methods: ['POST'])]
    public function approve(GrowthCampaign $campaign, Request $request, GrowthWorkflow $workflow): Response
    {
        return $this->contentAction($campaign, $request, 'growth_approve_', fn (GrowthContent $content) => $workflow->approve($campaign, $content, $this->getUser()));
    }

    #[Route('/campaigns/{id}/publish', name: 'campaign_publish', methods: ['POST'])]
    public function publish(GrowthCampaign $campaign, Request $request, GrowthWorkflow $workflow): Response
    {
        if (!$this->isCsrfTokenValid('growth_publish_'.$campaign->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
        }

        $destination = GrowthDestination::tryFrom((string) $request->request->get('destination')) ?? GrowthDestination::MAPSI_PUBLIC;
        $publication = $workflow->publish($campaign, $destination, $this->getUser());
        $this->addFlash($publication->getStatus()->value === 'failed' ? 'danger' : 'success', 'Publication: '.$publication->getStatus()->value);

        return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
    }

    #[Route('/campaigns/{id}/delete', name: 'campaign_delete', methods: ['POST'])]
    public function delete(GrowthCampaign $campaign, Request $request, GrowthWorkflow $workflow): Response
    {
        if ($this->isCsrfTokenValid('growth_delete_'.$campaign->getId(), (string) $request->request->get('_token'))) {
            $wasPublished = false;
            foreach ($campaign->getPublications() as $publication) {
                $wasPublished = $wasPublished || $publication->getStatus()->value === 'published';
            }
            $workflow->delete($campaign, $this->getUser());
            $this->addFlash('success', $wasPublished ? 'Campagne Growth archivée: ressource publiée conservée.' : 'Campagne Growth supprimée.');
        }

        return $this->redirectToRoute('admin_growth_campaigns');
    }

    private function contentAction(GrowthCampaign $campaign, Request $request, string $tokenPrefix, callable $action): Response
    {
        if (!$this->isCsrfTokenValid($tokenPrefix.$campaign->getId(), (string) $request->request->get('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide.');
        } elseif (($content = $campaign->getPrimaryContent()) instanceof GrowthContent) {
            $action($content);
            $this->addFlash('success', 'Statut Growth mis à jour.');
        }

        return $this->redirectToRoute('admin_growth_campaign_show', ['id' => $campaign->getId()]);
    }
}
