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
                'status' => 'READY',
                'selected_channels' => ['mapsi_site', 'oling_site'],
                'content_assets' => [],
                'publications' => [],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $factory = $this->factory($client);
        $actor = new InMemoryUser('admin@oling.fr', null, ['ROLE_ADMIN']);

        $factory->createCampaign($actor, 'MAPSI_MARKETING', 'Theme', ['mapsi_site', 'oling_site']);
        $factory->generateCampaign($actor, 'camp_1');
        $factory->updateAsset($actor, 'camp_1', 'asset_1', ['expected_version' => 3]);
        $factory->publishCampaign($actor, 'camp_1', ['mapsi_site']);

        self::assertSame('POST', $requests[0][0]);
        self::assertStringEndsWith('/studio-simple/campaigns', $requests[0][1]);
        self::assertSame(['mapsi_site', 'oling_site'], $requests[0][2]['selected_channels']);
        self::assertStringContainsString('/studio-simple/campaigns/camp_1/generate', $requests[1][1]);
        self::assertSame('PUT', $requests[2][0]);
        self::assertSame(['expected_version' => 3], $requests[2][2]);
        self::assertSame(['channels' => ['mapsi_site']], $requests[3][2]);
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
