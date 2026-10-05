<?php

namespace App\Tests;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Entity\GrowthPublication;
use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use App\Service\Growth\MapsiProductFactsClient;
use App\Service\Growth\MapsiPublicPublisher;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class GrowthPublishersAndFactsTest extends TestCase
{
    public function testMapsiPublicPublisherUsesNewsApiWhenConfigured(): void
    {
        $requests = [];
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options['headers'] ?? []];
            return new MockResponse('{"ok":true}', ['http_code' => 200]);
        });

        $campaign = (new GrowthCampaign())->setTitle('Campagne');
        $reflection = new \ReflectionProperty($campaign, 'id');
        $reflection->setAccessible(true);
        $reflection->setValue($campaign, 42);

        $content = (new GrowthContent())
            ->setCampaign($campaign)
            ->setTitle('Titre')
            ->setSlug('titre')
            ->setExcerpt('Extrait')
            ->setContentHtml('<p>Texte</p>')
            ->setMetaTitle('Meta')
            ->setMetaDescription('Description');

        $publication = (new GrowthPublication())
            ->setCampaign($campaign)
            ->setContent($content)
            ->setDestination(GrowthDestination::MAPSI_PUBLIC);

        (new MapsiPublicPublisher($client, 'https://mapsi.fr', 'token'))->publish($publication);

        self::assertCount(2, $requests);
        self::assertSame('POST', $requests[0][0]);
        self::assertStringEndsWith('/api/growth/news', $requests[0][1]);
        self::assertSame(GrowthPublicationStatus::PUBLISHED, $publication->getStatus());
    }

    public function testMapsiPublicPublisherIsDesignOnlyWithoutCredentials(): void
    {
        $publication = new GrowthPublication();
        (new MapsiPublicPublisher(new MockHttpClient(), '', ''))->publish($publication);

        self::assertSame(GrowthPublicationStatus::DESIGN_ONLY, $publication->getStatus());
    }

    public function testMapsiProductFactsClientIsReadOnly(): void
    {
        $client = new MapsiProductFactsClient(new MockHttpClient(new MockResponse('{"status":"ok"}')), 'https://app.mapsi.test', 'token');

        self::assertSame(['status' => 'ok'], $client->health());
        self::assertSame(['health', 'capabilities', 'contactSnapshot', 'usageSnapshot', 'productChanges'], array_values(array_filter(
            get_class_methods($client),
            static fn (string $method): bool => !str_starts_with($method, '__')
        )));
    }
}
