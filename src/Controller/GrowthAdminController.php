<?php

namespace App\Controller;

use App\Service\GrowthFactory\GrowthFactoryClient;
use App\Service\GrowthFactory\GrowthFactoryConflictException;
use App\Service\GrowthFactory\GrowthFactoryException;
use App\Service\GrowthFactory\GrowthFactoryView;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/growth')]
class GrowthAdminController extends AbstractController
{
    #[Route('', name: 'admin_growth_index', methods: ['GET'])]
    #[Route('/', name: 'admin_growth_index_slash', methods: ['GET'])]
    public function index(GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        try {
            $campaigns = array_map([$view, 'normalizeCampaign'], $client->listCampaigns($this->getUserOrDeny()));
            $available = true;
            $message = '';
        } catch (GrowthFactoryException $exception) {
            $campaigns = [];
            $available = false;
            $message = $exception->getMessage();
        }

        return $this->render('admin/growth/index.html.twig', [
            'available' => $available,
            'message' => $message,
            'campaigns' => $campaigns,
            'type_options' => $view->typeOptions(),
            'channel_options' => $view->channelOptions(),
            'parity_matrix' => $view->parityMatrix(),
        ]);
    }

    #[Route('/campaigns', name: 'admin_growth_campaign_create', methods: ['POST'])]
    public function create(Request $request, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        $this->validateCsrf($request, 'growth_campaign_create');
        $type = (string) $request->request->get('campaign_type', '');
        $theme = trim((string) $request->request->get('theme', ''));
        $channels = $view->allowedChannels($type, (array) $request->request->all('channels'));

        if ($theme === '' || $channels === []) {
            $this->addFlash('danger', 'Type, thème et canaux compatibles sont obligatoires.');
            return $this->redirectToRoute('admin_growth_index');
        }

        try {
            $campaign = $client->createCampaign($this->getUserOrDeny(), $type, $theme, $channels);
            $campaign = $client->generateCampaign($this->getUserOrDeny(), (string) ($campaign['id'] ?? ''));
            $this->addFlash('success', 'Campagne créée et contenus générés. Publication manuelle requise.');

            return $this->redirectToRoute('admin_growth_show', ['id' => (string) ($campaign['id'] ?? '')]);
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
            return $this->redirectToRoute('admin_growth_index');
        }
    }

    #[Route('/campaigns/{id}', name: 'admin_growth_show', methods: ['GET'])]
    public function show(string $id, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        try {
            $campaign = $view->normalizeCampaign($client->getCampaign($this->getUserOrDeny(), $id));
            $available = true;
            $message = '';
        } catch (GrowthFactoryException $exception) {
            $campaign = null;
            $available = false;
            $message = $exception->getMessage();
        }

        return $this->render('admin/growth/show.html.twig', [
            'available' => $available,
            'message' => $message,
            'campaign' => $campaign,
            'channel_options' => $view->channelOptions(),
        ]);
    }

    #[Route('/campaigns/{id}/generate', name: 'admin_growth_generate', methods: ['POST'])]
    public function generate(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_generate_'.$id);

        try {
            $client->generateCampaign($this->getUserOrDeny(), $id);
            $this->addFlash('success', 'Contenus régénérés. Publication manuelle requise.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Campagne déjà publiée ou état incompatible.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/assets/{assetId}', name: 'admin_growth_asset_update', methods: ['POST'])]
    public function updateAsset(string $id, string $assetId, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_asset_'.$id.'_'.$assetId);

        try {
            $client->updateAsset($this->getUserOrDeny(), $id, $assetId, [
                'title' => trim((string) $request->request->get('title', '')) ?: null,
                'content_html' => (string) $request->request->get('content_html', ''),
                'content_text' => (string) $request->request->get('content_text', ''),
                'expected_version' => (int) $request->request->get('expected_version', 0),
            ]);
            $this->addFlash('success', 'Contenu mis à jour.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Conflit de version : rechargez la campagne avant de modifier ce contenu.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/assets/{assetId}/preview', name: 'admin_growth_asset_preview', methods: ['GET'])]
    public function previewAsset(string $id, string $assetId, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        try {
            $campaign = $view->normalizeCampaign($client->getCampaign($this->getUserOrDeny(), $id));
            $asset = null;
            foreach ($campaign['content_assets'] as $item) {
                if (is_array($item) && (string) ($item['id'] ?? '') === $assetId) {
                    $asset = $item;
                    break;
                }
            }
            if ($asset === null) {
                throw $this->createNotFoundException();
            }

            return $this->render('admin/growth/preview.html.twig', [
                'campaign' => $campaign,
                'asset' => $asset,
                'channel_options' => $view->channelOptions(),
            ]);
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
            return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
        }
    }

    #[Route('/campaigns/{id}/publish', name: 'admin_growth_publish', methods: ['POST'])]
    public function publish(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_publish_'.$id);

        try {
            $client->publishCampaign($this->getUserOrDeny(), $id, (array) $request->request->all('channels'));
            $this->addFlash('success', 'Publication demandée à Growth Factory.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/delete', name: 'admin_growth_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_delete_'.$id);

        try {
            $client->deleteCampaign($this->getUserOrDeny(), $id);
            $this->addFlash('success', 'Campagne supprimée ou archivée par Growth Factory.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_index');
    }

    private function validateCsrf(Request $request, string $id): void
    {
        if (!$this->isCsrfTokenValid($id, (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }
    }

    private function getUserOrDeny(): \Symfony\Component\Security\Core\User\UserInterface
    {
        return $this->getUser() ?? throw $this->createAccessDeniedException();
    }
}
