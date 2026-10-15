<?php

namespace App\Tests;

use App\Service\GrowthFactory\GrowthFactoryClient;
use App\Service\GrowthFactory\GrowthFactoryConflictException;
use App\Service\GrowthFactory\GrowthFactoryException;
use App\Service\GrowthFactory\GrowthFactoryJwtEncoder;
use App\Service\GrowthFactory\GrowthFactoryRoleMapper;
use App\Service\GrowthFactory\GrowthFactoryUnavailableException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Component\Security\Core\User\InMemoryUser;

final class GrowthFactoryClientTest extends TestCase
{
    public function testIllustrationUsesTheExistingAsyncEndpointAndExactAssetVersion(): void
    {
        $calls = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $calls[] = [$method, $url, json_decode($options['body'], true)];
            return new MockResponse('{"id":"campaign","generation":{"status":"PENDING"}}', ['http_code' => 202]);
        });
        $result = $this->factory($http)->generateAssetImage(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'campaign', 'oling-fr', 7, 'Article illustration');
        self::assertSame('PENDING', $result['generation']['status']);
        self::assertSame([['POST', 'https://growth.test/studio-simple/campaigns/campaign/assets/oling-fr/image', [
            'visual_brief' => 'Article illustration', 'expected_version' => 7,
        ]]], $calls);
    }

    public function testValidateAndPublishUsesOnlyTheSelectedArticleAndItsApprovedHash(): void
    {
        $calls = [];
        $http = new MockHttpClient(function (string $method, string $url, array $options) use (&$calls): MockResponse {
            $body = isset($options['body']) && is_string($options['body']) ? json_decode($options['body'], true) : null;
            $calls[] = [$method, $url, $body];
            $asset = ['id' => 'selected-fr', 'version' => 4, 'status' => 'READY_FOR_REVIEW'];
            if (str_ends_with($url, '/publication-readiness')) {
                return new MockResponse(json_encode(['data' => ['metadata' => [
                    'mode' => 'live', 'connector_configured' => true, 'feature_enabled' => true,
                    'reasons' => ['Asset is not approved.', 'Approved content hash no longer matches the current asset.'],
                ]]]));
            }
            if (str_ends_with($url, '/approve')) {
                $asset['status'] = 'APPROVED';
                $asset['approved_content_hash'] = 'exact-approved-hash';
            }
            return new MockResponse(json_encode(['id' => 'campaign', 'content_assets' => [$asset, ['id' => 'other-es', 'status' => 'READY_FOR_REVIEW']]]));
        });
        $this->factory($http)->validateAndPublishAsset(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'campaign', 'selected-fr', 4);
        self::assertCount(5, $calls);
        self::assertStringEndsWith('/selected-fr/review', $calls[2][1]);
        self::assertStringEndsWith('/selected-fr/approve', $calls[3][1]);
        self::assertStringEndsWith('/selected-fr/publish', $calls[4][1]);
        self::assertSame(4, $calls[4][2]['expected_version']);
        self::assertSame('exact-approved-hash', $calls[4][2]['approved_content_hash']);
        self::assertStringNotContainsString('other-es', implode(' ', array_column($calls, 1)));
    }

    public function testMockConnectorIsRefusedBeforeAnyApprovalOrPublication(): void
    {
        $calls = 0;
        $http = new MockHttpClient(function (string $method, string $url) use (&$calls): MockResponse {
            ++$calls;
            self::assertSame('GET', $method);
            return new MockResponse(json_encode(str_ends_with($url, '/publication-readiness')
                ? ['data' => ['metadata' => ['mode' => 'mock', 'reasons' => []]]]
                : ['content_assets' => [['id' => 'asset', 'version' => 1, 'status' => 'READY_FOR_REVIEW']]]));
        });
        try {
            $this->factory($http)->validateAndPublishAsset(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'campaign', 'asset', 1);
            self::fail('Mock mode must never publish.');
        } catch (GrowthFactoryException $exception) {
            self::assertSame(2, $calls);
        }
    }

    public function testCreateGenerateUpdateAndPublishUseStudioSimpleContract(): void
    {
        $requests = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $body = isset($options['body']) && is_string($options['body']) ? json_decode($options['body'], true) : null;
            $requests[] = [$method, $url, $body, $options['headers'] ?? []];

            return new MockResponse(json_encode([
                'id' => 'camp_1',
                'name' => 'Theme',
                'campaign_type' => 'MAPSI_MARKETING',
                'theme' => 'Theme',
                'brief' => 'Brief',
                'status' => 'READY',
                'selected_channels' => ['mapsi_site', 'oling_site'],
                'content_assets' => [],
                'publications' => [],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $factory = $this->factory($client);
        $actor = new InMemoryUser('admin@oling.fr', null, ['ROLE_ADMIN']);

        $factory->createCampaign($actor, 'MAPSI_MARKETING', 'Theme', ['mapsi_site', 'oling_site'], 'Theme', 'Brief');
        $factory->generateCampaign($actor, 'camp_1');
        $factory->updateAsset($actor, 'camp_1', 'asset_1', ['expected_version' => 3]);
        $factory->reviewAsset($actor, 'camp_1', 'asset_1', 4, 'ok');
        $factory->approveAsset($actor, 'camp_1', 'asset_1', 4, 'ok');
        $factory->publishCampaign($actor, 'camp_1', ['mapsi_site']);
        $factory->unpublishCampaign($actor, 'camp_1', ['mapsi_site']);
        $factory->archiveCampaign($actor, 'camp_1');

        self::assertSame('POST', $requests[0][0]);
        self::assertStringEndsWith('/studio-simple/campaigns', $requests[0][1]);
        self::assertSame(['mapsi_site', 'oling_site'], $requests[0][2]['selected_channels']);
        self::assertSame('Brief', $requests[0][2]['brief']);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/generate', $requests[1][1]);
        self::assertSame('PUT', $requests[2][0]);
        self::assertSame(['expected_version' => 3], $requests[2][2]);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/assets/asset_1/review', $requests[3][1]);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/assets/asset_1/approve', $requests[4][1]);
        self::assertSame(['channels' => ['mapsi_site']], $requests[5][2]);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/unpublish', $requests[6][1]);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/archive', $requests[7][1]);
        self::assertStringContainsString('Authorization: Bearer ', implode("\n", $requests[0][3]));
    }

    public function testFeatureFlagDisabledPreventsHttpCall(): void
    {
        $factory = new GrowthFactoryClient(new MockHttpClient(), $this->jwt(), new NullLogger(), false, 'https://growth.test', 1, 1);

        $this->expectException(GrowthFactoryUnavailableException::class);
        $factory->listCampaigns(new InMemoryUser('admin', null, ['ROLE_ADMIN']));
    }

    public function testConflictStatusIsExplicit(): void
    {
        $factory = $this->factory(new MockHttpClient(new MockResponse('{"detail":"version conflict"}', ['http_code' => 409])));

        $this->expectException(GrowthFactoryConflictException::class);
        $factory->updateAsset(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'camp', 'asset', []);
    }

    public function testStructuredConflictDisplaysTheBusinessMessage(): void
    {
        $factory = $this->factory(new MockHttpClient(new MockResponse('{"detail":{"code":"PUBLICATION_CONFLICT","message":"This slug is already used."}}', ['http_code' => 409])));
        $this->expectException(GrowthFactoryConflictException::class);
        $this->expectExceptionMessage('This slug is already used.');
        $factory->publishAsset(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'asset');
    }

    public function testInvalidSuccessPayloadIsNotAcceptedAsSuccess(): void
    {
        $factory = $this->factory(new MockHttpClient(new MockResponse('<html>gateway error</html>', ['http_code' => 200])));
        $this->expectException(GrowthFactoryUnavailableException::class);
        $factory->getCampaign(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'campaign');
    }

    public function testValidationStatusIsExplicit(): void
    {
        $factory = $this->factory(new MockHttpClient(new MockResponse('{"detail":"invalid channel"}', ['http_code' => 422])));

        $this->expectException(GrowthFactoryException::class);
        $this->expectExceptionMessage('Requête Growth invalide: invalid channel');
        $factory->createCampaign(new InMemoryUser('admin', null, ['ROLE_ADMIN']), 'OLING', 'Theme', ['bad']);
    }

    public function testRateLimitStatusIsUnavailable(): void
    {
        $factory = $this->factory(new MockHttpClient(new MockResponse('{"detail":"too many requests"}', ['http_code' => 429])));

        $this->expectException(GrowthFactoryUnavailableException::class);
        $this->expectExceptionMessage('Growth Factory limite temporairement les requêtes.');
        $factory->listCampaigns(new InMemoryUser('admin', null, ['ROLE_ADMIN']));
    }

    private function factory(MockHttpClient $client): GrowthFactoryClient
    {
        return new GrowthFactoryClient($client, $this->jwt(), new NullLogger(), true, 'https://growth.test', 1, 1);
    }

    private function jwt(): GrowthFactoryJwtEncoder
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($key, $pem);

        return new GrowthFactoryJwtEncoder(new GrowthFactoryRoleMapper(), 'oling.test', 'mapsi-growth-admin', 60, 'test-key', $pem, null);
    }
}
