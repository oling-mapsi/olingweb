<?php

namespace App\Service\GrowthFactory;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class GrowthFactoryClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly GrowthFactoryJwtEncoder $jwtEncoder,
        private readonly LoggerInterface $logger,
        #[Autowire(param: 'growth_factory_enabled')]
        private readonly bool $enabled,
        #[Autowire(param: 'growth_factory_base_url')]
        private readonly string $baseUrl,
        #[Autowire(param: 'growth_factory_timeout_seconds')]
        private readonly float $timeoutSeconds,
        #[Autowire(param: 'growth_factory_generate_timeout_seconds')]
        private readonly float $generateTimeoutSeconds,
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getAssetImage(UserInterface $actor, string $assetId): array
    {
        if (!$this->enabled || trim($this->baseUrl) === '') {
            throw new GrowthFactoryUnavailableException('Growth est indisponible.');
        }
        try {
            $response = $this->httpClient->request('GET', rtrim($this->baseUrl, '/').'/studio-simple/assets/'.rawurlencode($assetId).'/image', [
                'headers' => ['Authorization' => 'Bearer '.$this->jwtEncoder->encode($actor, bin2hex(random_bytes(16)))],
                'timeout' => $this->timeoutSeconds,
                'max_redirects' => 0,
            ]);
            if ($response->getStatusCode() !== 200) {
                throw new GrowthFactoryException('Illustration indisponible.');
            }
            $content = $response->getContent(false);
            $info = @getimagesizefromstring($content);
            if (!$info || !in_array($info['mime'], ['image/png', 'image/jpeg', 'image/webp'], true) || strlen($content) > 12582912) {
                throw new GrowthFactoryException('Format d’illustration invalide.');
            }
            return ['content' => $content, 'content_type' => $info['mime']];
        } catch (TransportExceptionInterface $exception) {
            throw new GrowthFactoryUnavailableException('Illustration temporairement indisponible.', previous: $exception);
        }
    }

    public function listCampaigns(UserInterface $actor): array
    {
        return $this->request($actor, 'GET', '/studio-simple/campaigns');
    }

    public function dashboard(UserInterface $actor): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/dashboard');
    }

    public function listAdvancedCampaigns(UserInterface $actor, int $page = 1, int $pageSize = 100): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/campaigns?page='.max(1, $page).'&page_size='.max(1, min(100, $pageSize)));
    }

    public function getAdvancedCampaign(UserInterface $actor, string $id): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/campaigns/'.rawurlencode($id));
    }

    public function listAdvancedAssets(UserInterface $actor, string $id): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/campaigns/'.rawurlencode($id).'/assets?page_size=100');
    }

    public function listChannels(UserInterface $actor): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/channels');
    }

    public function listAuditEvents(UserInterface $actor): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/audit-events?page_size=20');
    }

    public function listWeeklyPacks(UserInterface $actor): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/weekly-packs?page_size=20');
    }

    public function healthCheckChannel(UserInterface $actor, string $channel): array
    {
        return $this->request($actor, 'POST', '/api/admin/v1/channels/'.rawurlencode($channel).'/health-check', []);
    }

    public function getCampaign(UserInterface $actor, string $id): array
    {
        return $this->request($actor, 'GET', '/studio-simple/campaigns/'.rawurlencode($id));
    }

    public function createCampaign(UserInterface $actor, string $type, string $theme, array $channels, ?string $name = null, string $brief = ''): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns', [
            'name' => $name ?: $theme,
            'campaign_type' => $type,
            'theme' => $theme,
            'brief' => $brief,
            'selected_channels' => array_values($channels),
        ]);
    }

    public function generateCampaign(UserInterface $actor, string $id): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($id).'/generate', null, $this->generateTimeoutSeconds);
    }

    public function updateAsset(UserInterface $actor, string $campaignId, string $assetId, array $payload): array
    {
        return $this->request($actor, 'PUT', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId), $payload);
    }

    public function reviewAsset(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion, string $comment = ''): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId).'/review', [
            'comment' => $comment,
            'expected_version' => $expectedVersion,
        ]);
    }

    public function repairAsset(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId).'/repair', ['expected_version' => $expectedVersion]);
    }

    public function approveAsset(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion, string $comment = '', bool $confirmEditorialReview = false): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId).'/approve', [
            'confirm_editorial_review' => $confirmEditorialReview,
            'comment' => $comment,
            'expected_version' => $expectedVersion,
        ]);
    }

    public function generateAssetImage(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion, string $visualBrief = '', ?string $sourceAssetId = null): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId).'/image', [
            'visual_brief' => $visualBrief,
            'expected_version' => $expectedVersion,
            ...($sourceAssetId ? ['source_asset_id' => $sourceAssetId] : []),
        ], $this->generateTimeoutSeconds);
    }

    public function prepareAssetSeoGeo(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($campaignId).'/assets/'.rawurlencode($assetId).'/seo-geo', [
            'expected_version' => $expectedVersion,
        ], $this->generateTimeoutSeconds);
    }

    public function publishCampaign(UserInterface $actor, string $id, array $channels): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($id).'/publish', ['channels' => array_values($channels)]);
    }

    public function publishAsset(UserInterface $actor, string $assetId, ?int $expectedVersion = null, ?string $approvedHash = null): array
    {
        return $this->request($actor, 'POST', '/api/admin/v1/assets/'.rawurlencode($assetId).'/publish', [
            'idempotency_key' => bin2hex(random_bytes(16)), 'comment' => 'Publication manuelle OLING Admin',
            'expected_version' => $expectedVersion, 'approved_content_hash' => $approvedHash,
        ], max(60, $this->timeoutSeconds));
    }

    public function getAssetPublicationReadiness(UserInterface $actor, string $assetId): array
    {
        return $this->request($actor, 'GET', '/api/admin/v1/assets/'.rawurlencode($assetId).'/publication-readiness');
    }

    public function validateAndPublishAsset(UserInterface $actor, string $campaignId, string $assetId, int $expectedVersion): array
    {
        $campaign = $this->getCampaign($actor, $campaignId);
        $find = static function (array $campaign) use ($assetId, $expectedVersion): array {
            foreach ($campaign['content_assets'] ?? [] as $asset) {
                if (($asset['id'] ?? '') === $assetId && (int) ($asset['version'] ?? 0) === $expectedVersion) {
                    return $asset;
                }
            }
            throw new GrowthFactoryConflictException('Cet article a change. Rechargez-le avant de valider sa publication.');
        };
        $asset = $find($campaign);
        if (($asset['status'] ?? '') === 'PUBLISHED') {
            return ['already_published' => true, 'public_url' => $asset['public_url'] ?? ''];
        }
        $response = $this->getAssetPublicationReadiness($actor, $assetId);
        $readiness = (array) ($response['data']['metadata'] ?? []);
        $blocking = array_diff($readiness['reasons'] ?? ['Publication indisponible.'], [
            'Asset is not approved.', 'Approved content hash no longer matches the current asset.',
        ]);
        $humanReviewPending = !empty($readiness['human_review_pending']);
        if ($humanReviewPending) {
            $blocking = array_diff($blocking, ['Blocking editorial quality issues remain.']);
        }
        if ($blocking || ($readiness['mode'] ?? '') !== 'live' || empty($readiness['connector_configured']) || empty($readiness['feature_enabled']) || !empty($readiness['channel_kill_switch']) || !empty($readiness['global_kill_switch']) || (!empty($readiness['quality_blocking']) && !$humanReviewPending)) {
            throw new GrowthFactoryException('Publication impossible : '.implode(' ', $blocking ?: ['Le connecteur reel doit etre active.']));
        }
        if (($asset['status'] ?? '') !== 'APPROVED') {
            if (($asset['status'] ?? '') !== 'REVIEWED') {
                $this->reviewAsset($actor, $campaignId, $assetId, $expectedVersion, 'Relu et valide avant publication manuelle');
            }
            $campaign = $this->approveAsset($actor, $campaignId, $assetId, $expectedVersion, 'Validation du texte et de ses affirmations, puis publication confirmees dans OLING', true);
            $asset = $find($campaign);
        }
        return $this->publishAsset($actor, $assetId, $expectedVersion, (string) ($asset['approved_content_hash'] ?? ''));
    }

    public function unpublishAsset(UserInterface $actor, string $assetId): array
    {
        return $this->request($actor, 'POST', '/api/admin/v1/assets/'.rawurlencode($assetId).'/unpublish', ['comment' => 'Depublication manuelle OLING Admin'], max(60, $this->timeoutSeconds));
    }

    public function unpublishCampaign(UserInterface $actor, string $id, array $channels): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($id).'/unpublish', ['channels' => array_values($channels)]);
    }

    public function archiveCampaign(UserInterface $actor, string $id): array
    {
        return $this->request($actor, 'POST', '/studio-simple/campaigns/'.rawurlencode($id).'/archive');
    }

    public function deleteCampaign(UserInterface $actor, string $id): void
    {
        $this->request($actor, 'DELETE', '/studio-simple/campaigns/'.rawurlencode($id));
    }

    private function request(UserInterface $actor, string $method, string $path, ?array $json = null, ?float $timeout = null): array
    {
        if (!$this->enabled) {
            throw new GrowthFactoryUnavailableException('Growth est désactivé.');
        }
        if (trim($this->baseUrl) === '') {
            throw new GrowthFactoryUnavailableException('Growth Factory n’est pas configuré.');
        }

        $correlationId = bin2hex(random_bytes(16));
        $options = [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->jwtEncoder->encode($actor, $correlationId),
                'X-Correlation-ID' => $correlationId,
                'Idempotency-Key' => $correlationId,
            ],
            'timeout' => $timeout ?? $this->timeoutSeconds,
        ];
        if ($json !== null) {
            $options['headers']['Content-Type'] = 'application/json';
            $options['json'] = $json;
        }

        try {
            $response = $this->httpClient->request($method, rtrim($this->baseUrl, '/').$path, $options);
            $status = $response->getStatusCode();
            $body = trim($response->getContent(false));
            $payload = $body !== '' ? json_decode($body, true, 512, JSON_THROW_ON_ERROR) : [];
        } catch (TransportExceptionInterface|\JsonException|\RuntimeException $exception) {
            $this->logger->warning('growth_factory_unavailable', ['method' => $method, 'path' => $path, 'error' => $exception->getMessage()]);
            throw new GrowthFactoryUnavailableException('Growth Factory est indisponible.', previous: $exception);
        }

        if ($status >= 200 && $status < 300) {
            return is_array($payload) ? $payload : [];
        }
        $detail = is_array($payload) ? ($payload['detail'] ?? $payload['message'] ?? null) : null;
        if (is_array($detail)) {
            $detail = $detail['message'] ?? $detail['detail'] ?? $detail['code'] ?? null;
        }
        $message = is_string($detail) && $detail !== '' ? $detail : 'Erreur Growth Factory.';

        throw match ($status) {
            404 => new GrowthFactoryNotFoundException($message),
            409 => new GrowthFactoryConflictException($message),
            401, 403 => new GrowthFactoryException('Accès Growth refusé.'),
            422 => new GrowthFactoryException('Requête Growth invalide: '.$message),
            429 => new GrowthFactoryUnavailableException('Growth Factory limite temporairement les requêtes.'),
            default => $status >= 500 ? new GrowthFactoryUnavailableException('Growth Factory est indisponible.') : new GrowthFactoryException($message),
        };
    }
}
