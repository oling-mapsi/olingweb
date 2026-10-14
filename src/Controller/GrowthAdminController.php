<?php

namespace App\Controller;

use App\Service\GrowthFactory\GrowthFactoryClient;
use App\Service\GrowthFactory\GrowthFactoryConflictException;
use App\Service\GrowthFactory\GrowthFactoryException;
use App\Service\GrowthFactory\GrowthFactoryView;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/growth')]
class GrowthAdminController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        #[Autowire(param: 'growth_factory_allowed_admin_emails')]
        private readonly ?string $allowedAdminEmails = null,
    ) {
    }

    #[Route('', name: 'admin_growth_index', methods: ['GET'])]
    #[Route('/', name: 'admin_growth_index_slash', methods: ['GET'])]
    public function index(Request $request, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        $actor = $this->getUserOrDeny();
        $query = trim((string) $request->query->get('q', ''));
        $status = trim((string) $request->query->get('status', ''));
        $page = max(1, (int) $request->query->get('page', 1));
        $campaigns = [];
        $available = true;
        $message = '';

        try {
            $campaigns = array_map([$view, 'normalizeCampaign'], $client->listCampaigns($actor));
        } catch (GrowthFactoryException $exception) {
            $available = false;
            $message = $exception->getMessage();
        }
        $history = $this->paginateRows($this->filterHistoryRows($this->buildHistoryRows($campaigns, []), $query, $status), $page);

        return $this->render('admin/growth/index.html.twig', [
            'available' => $available,
            'message' => $message,
            'campaigns' => $campaigns,
            'history' => $history,
            'filters' => ['q' => $query, 'status' => $status, 'page' => $page],
            'destination_options' => $view->destinationOptions(),
        ]);
    }

    #[Route('/articles', name: 'admin_growth_articles', methods: ['GET'])]
    public function articles(Request $request, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        return $this->index($request, $client, $view);
    }

    #[Route('/settings', name: 'admin_growth_settings', methods: ['GET'])]
    public function settings(GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        $actor = $this->getUserOrDeny();
        $errors = [];
        $advancedCampaigns = [];
        $dashboard = [];
        $channels = [];
        $auditEvents = [];
        $weeklyPacks = [];
        $available = true;
        $message = '';

        try {
            $advancedCampaigns = (array) (($client->listAdvancedCampaigns($actor)['data'] ?? []));
            $dashboard = (array) (($client->dashboard($actor)['data'] ?? []));
            $channels = (array) (($client->listChannels($actor)['data'] ?? []));
            $auditEvents = (array) (($client->listAuditEvents($actor)['data'] ?? []));
            $weeklyPacks = (array) (($client->listWeeklyPacks($actor)['data'] ?? []));
        } catch (GrowthFactoryException $exception) {
            $available = false;
            $message = $exception->getMessage();
            $errors['settings'] = $exception->getMessage();
        }

        return $this->render('admin/growth/settings.html.twig', [
            'available' => $available,
            'message' => $message,
            'advanced_campaigns' => $advancedCampaigns,
            'dashboard' => $dashboard,
            'channels' => $channels,
            'audit_events' => $auditEvents,
            'weekly_packs' => $weeklyPacks,
            'section_errors' => $errors,
            'parity_matrix' => $view->parityMatrix(),
        ]);
    }

    #[Route('/channels/{channel}/health-check', name: 'admin_growth_channel_health_check', methods: ['POST'])]
    public function healthCheckChannel(string $channel, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_channel_'.$channel);

        try {
            $client->healthCheckChannel($this->getUserOrDeny(), $channel);
            $this->addFlash('success', 'Contrôle du canal exécuté.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_index');
    }

    #[Route('/campaigns', name: 'admin_growth_campaign_create', methods: ['POST'])]
    public function create(Request $request, GrowthFactoryClient $client, GrowthFactoryView $view): Response
    {
        $this->validateCsrf($request, 'growth_campaign_create');
        $destination = (string) $request->request->get('destination', '');
        $subject = trim((string) $request->request->get('subject', ''));
        $prompt = trim((string) $request->request->get('prompt', ''));
        $type = (string) $request->request->get('campaign_type', '');
        $theme = trim((string) $request->request->get('theme', ''));
        $channels = [];

        if ($destination !== '') {
            $article = $view->articleDestination($destination);
            $type = (string) $article['campaign_type'];
            $channels = (array) $article['channels'];
            $theme = $subject;
        } else {
            $subject = $theme;
            $channels = $view->allowedChannels($type, (array) $request->request->all('channels'));
        }

        if ($subject === '' || $theme === '' || $channels === []) {
            $this->addFlash('danger', 'Destination, sujet et brief éditorial sont obligatoires.');
            return $this->redirectToRoute('admin_growth_index');
        }

        try {
            $campaign = $client->createCampaign($this->getUserOrDeny(), $type, $theme, $channels, $subject, $prompt);
            $campaignId = (string) ($campaign['id'] ?? '');
            try {
                $client->generateCampaign($this->getUserOrDeny(), $campaignId);
                $this->addFlash('success', 'Article généré. Relisez, éditez et approuvez avant toute publication.');
            } catch (GrowthFactoryException $exception) {
                $correlationId = bin2hex(random_bytes(8));
                $this->logger->warning('growth_generation_failed_after_create', ['campaign_id' => $campaignId, 'correlation_id' => $correlationId, 'error' => $exception->getMessage()]);
                $this->addFlash('danger', 'Campagne créée en brouillon, mais la génération a échoué. Vous pouvez réessayer depuis cette fiche. Référence '.$correlationId.'.');
            }

            return $this->redirectToRoute('admin_growth_show', ['id' => $campaignId]);
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
            'publication_enabled' => false,
            'editorial_links' => [
                'mapsi_site' => $view->editorialLinksForChannel('mapsi_site'),
                'oling_site' => $view->editorialLinksForChannel('oling_site'),
            ],
        ]);
    }

    #[Route('/campaigns/{id}/status', name: 'admin_growth_status', methods: ['GET'])]
    public function status(string $id, GrowthFactoryClient $client, GrowthFactoryView $view): JsonResponse
    {
        try {
            $campaign = $view->normalizeCampaign($client->getCampaign($this->getUserOrDeny(), $id));
            $assets = [];
            foreach ($campaign['content_assets'] as $asset) {
                if (!is_array($asset)) {
                    continue;
                }
                $assets[] = [
                    'id' => (string) ($asset['id'] ?? ''),
                    'channel' => (string) ($asset['channel'] ?? ''),
                    'locale' => (string) ($asset['locale'] ?? 'fr-FR'),
                    'status' => (string) ($asset['status'] ?? ''),
                    'title' => (string) ($asset['title'] ?? ''),
                ];
            }

            return $this->json([
                'available' => true,
                'status' => (string) ($campaign['status'] ?? ''),
                'generation' => $campaign['generation'] ?? null,
                'assets' => $assets,
            ]);
        } catch (GrowthFactoryException $exception) {
            return $this->json(['available' => false, 'message' => $exception->getMessage()], 503);
        }
    }

    #[Route('/advanced-campaigns/{id}', name: 'admin_growth_advanced_show', methods: ['GET'])]
    public function showAdvanced(string $id, GrowthFactoryClient $client): Response
    {
        try {
            $campaign = (array) (($client->getAdvancedCampaign($this->getUserOrDeny(), $id)['data'] ?? []));
            $assets = (array) (($client->listAdvancedAssets($this->getUserOrDeny(), $id)['data'] ?? []));
            $available = true;
            $message = '';
        } catch (GrowthFactoryException $exception) {
            $campaign = [];
            $assets = [];
            $available = false;
            $message = $exception->getMessage();
        }

        return $this->render('admin/growth/advanced_show.html.twig', [
            'available' => $available,
            'message' => $message,
            'campaign' => $campaign,
            'assets' => $assets,
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

    #[Route('/campaigns/{id}/assets/{assetId}/review', name: 'admin_growth_asset_review', methods: ['POST'])]
    public function reviewAsset(string $id, string $assetId, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_review_'.$id.'_'.$assetId);

        try {
            $client->reviewAsset($this->getUserOrDeny(), $id, $assetId, (int) $request->request->get('expected_version', 0), trim((string) $request->request->get('comment', '')));
            $this->addFlash('success', 'Contenu marqué comme revu.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Revue impossible : rechargez la campagne ou vérifiez son état.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/assets/{assetId}/approve', name: 'admin_growth_asset_approve', methods: ['POST'])]
    public function approveAsset(string $id, string $assetId, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_approve_'.$id.'_'.$assetId);

        try {
            $client->approveAsset($this->getUserOrDeny(), $id, $assetId, (int) $request->request->get('expected_version', 0), trim((string) $request->request->get('comment', '')));
            $this->addFlash('success', 'Contenu approuvé.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Approbation impossible : la version ou l’état du contenu a changé.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/assets/{assetId}/image', name: 'admin_growth_asset_image', methods: ['POST'])]
    public function generateAssetImage(string $id, string $assetId, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_image_'.$id.'_'.$assetId);

        try {
            $client->generateAssetImage($this->getUserOrDeny(), $id, $assetId, (int) $request->request->get('expected_version', 0), trim((string) $request->request->get('visual_brief', '')));
            $this->addFlash('success', 'Illustration générée et conservée.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Conflit de version : rechargez la campagne avant de générer l’illustration.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/assets/{assetId}/seo-geo', name: 'admin_growth_asset_seo_geo', methods: ['POST'])]
    public function prepareAssetSeoGeo(string $id, string $assetId, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_seo_geo_'.$id.'_'.$assetId);

        try {
            $client->prepareAssetSeoGeo($this->getUserOrDeny(), $id, $assetId, (int) $request->request->get('expected_version', 0));
            $this->addFlash('success', 'Contrôles SEO/GEO calculés et liens enregistrés.');
        } catch (GrowthFactoryConflictException) {
            $this->addFlash('danger', 'Conflit de version : rechargez la campagne avant le contrôle SEO/GEO.');
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

    #[Route('/campaigns/{id}/unpublish', name: 'admin_growth_unpublish', methods: ['POST'])]
    public function unpublish(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_unpublish_'.$id);

        try {
            $client->unpublishCampaign($this->getUserOrDeny(), $id, (array) $request->request->all('channels'));
            $this->addFlash('success', 'Dépublication demandée à Growth Factory.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
        }

        return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
    }

    #[Route('/campaigns/{id}/archive', name: 'admin_growth_archive', methods: ['POST'])]
    public function archive(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_archive_'.$id);

        try {
            $client->archiveCampaign($this->getUserOrDeny(), $id);
            $this->addFlash('success', 'Campagne archivée.');
        } catch (GrowthFactoryException $exception) {
            $this->addFlash('danger', $exception->getMessage());
            return $this->redirectToRoute('admin_growth_show', ['id' => $id]);
        }

        return $this->redirectToRoute('admin_growth_index');
    }

    #[Route('/campaigns/{id}/delete', name: 'admin_growth_delete', methods: ['POST'])]
    public function delete(string $id, Request $request, GrowthFactoryClient $client): Response
    {
        $this->validateCsrf($request, 'growth_delete_'.$id);

        try {
            $client->deleteCampaign($this->getUserOrDeny(), $id);
            $this->addFlash('success', 'Campagne supprimée.');
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

    private function buildHistoryRows(array $campaigns, array $advancedCampaigns): array
    {
        $rows = [];
        foreach ($campaigns as $campaign) {
            $id = (string) ($campaign['id'] ?? '');
            $rows[$id] = [
                'id' => $id,
                'kind' => 'simple',
                'title' => (string) (($campaign['name'] ?? '') ?: ($campaign['theme'] ?? '')),
                'type' => (string) ($campaign['campaign_type'] ?? 'SIMPLE'),
                'status' => (string) ($campaign['status'] ?? ''),
                'channels' => implode(', ', (array) ($campaign['selected_channels'] ?? [])),
                'created_at' => (string) ($campaign['created_at'] ?? ''),
                'updated_at' => (string) ($campaign['updated_at'] ?? ''),
                'asset_count' => count((array) ($campaign['content_assets'] ?? [])),
            ];
        }
        foreach ($advancedCampaigns as $campaign) {
            $id = (string) ($campaign['id'] ?? '');
            $rows[$id] ??= [
                'id' => $id,
                'kind' => 'advanced',
                'title' => (string) (($campaign['title'] ?? '') ?: $id),
                'type' => (string) ($campaign['objective'] ?? 'ADVANCED'),
                'status' => (string) ($campaign['status'] ?? ''),
                'channels' => '',
                'created_at' => (string) ($campaign['created_at'] ?? ''),
                'updated_at' => '',
                'asset_count' => (int) ($campaign['asset_count'] ?? 0),
            ];
        }

        return array_values($rows);
    }

    private function filterHistoryRows(array $rows, string $query, string $status): array
    {
        return array_values(array_filter($rows, static function (array $row) use ($query, $status): bool {
            if ($status !== '' && strcasecmp((string) $row['status'], $status) !== 0) {
                return false;
            }
            if ($query === '') {
                return true;
            }
            return str_contains(mb_strtolower(implode(' ', $row)), mb_strtolower($query));
        }));
    }

    private function paginateRows(array $rows, int $page): array
    {
        $pageSize = 20;
        return [
            'items' => array_slice($rows, ($page - 1) * $pageSize, $pageSize),
            'page' => $page,
            'page_size' => $pageSize,
            'total' => count($rows),
            'pages' => max(1, (int) ceil(count($rows) / $pageSize)),
        ];
    }

    private function getUserOrDeny(): \Symfony\Component\Security\Core\User\UserInterface
    {
        $user = $this->getUser() ?? throw $this->createAccessDeniedException();
        $allowed = array_filter(array_map(
            static fn (string $email): string => mb_strtolower(trim($email)),
            explode(',', $this->allowedAdminEmails ?? '')
        ));
        $identifiers = [mb_strtolower($user->getUserIdentifier())];
        if (method_exists($user, 'getEmail')) {
            $identifiers[] = mb_strtolower((string) $user->getEmail());
        }
        if ($allowed !== [] && array_intersect($identifiers, $allowed) === []) {
            throw $this->createAccessDeniedException('Growth est limite aux administrateurs pilotes.');
        }

        return $user;
    }
}
