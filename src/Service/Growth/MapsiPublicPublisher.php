<?php

namespace App\Service\Growth;

use App\Entity\GrowthPublication;
use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapsiPublicPublisher implements GrowthPublisherInterface
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $baseUrl,
        private readonly string $token,
    ) {
    }

    public function supports(GrowthDestination $destination): bool
    {
        return $destination === GrowthDestination::MAPSI_PUBLIC;
    }

    public function publish(GrowthPublication $publication): void
    {
        if (trim($this->baseUrl) === '' || trim($this->token) === '') {
            $publication
                ->setStatus(GrowthPublicationStatus::DESIGN_ONLY)
                ->setLastError('MAPSI_PUBLIC API credentials are not configured.');

            return;
        }

        $content = $publication->getContent();
        $campaign = $publication->getCampaign();
        if ($content === null || $campaign === null) {
            throw new \RuntimeException('Publication is incomplete.');
        }

        $externalId = 'oling-growth-campaign-'.$campaign->getId();
        $payload = [
            'external_id' => $externalId,
            'title' => $content->getTitle(),
            'slug' => $content->getSlug(),
            'excerpt' => $content->getExcerpt(),
            'content_html' => $content->getContentHtml(),
            'meta_title' => $content->getMetaTitle(),
            'meta_description' => $content->getMetaDescription(),
            'canonical_url' => 'https://mapsi.fr/actualites/'.$content->getSlug(),
            'categories' => $content->getCategories(),
            'tags' => $content->getTags(),
            'author_display_name' => $content->getAuthorDisplayName(),
            'source_campaign_id' => (string) $campaign->getId(),
            'publication_date' => (new \DateTimeImmutable())->format(DATE_ATOM),
        ];

        $draftResponse = $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/api/growth/news', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
            'timeout' => 15,
        ]);
        if ($draftResponse->getStatusCode() >= 300) {
            throw new \RuntimeException('MAPSI_PUBLIC draft request failed.');
        }

        $publishResponse = $this->httpClient->request('POST', rtrim($this->baseUrl, '/').'/api/growth/news/'.rawurlencode($externalId).'/publish', [
            'headers' => [
                'Authorization' => 'Bearer '.$this->token,
                'Idempotency-Key' => 'oling-growth-'.$campaign->getId().'-'.$content->getId(),
            ],
            'timeout' => 15,
        ]);
        if ($publishResponse->getStatusCode() >= 300) {
            throw new \RuntimeException('MAPSI_PUBLIC publish request failed.');
        }

        $publication
            ->setStatus(GrowthPublicationStatus::PUBLISHED)
            ->setExternalIdentifier($externalId)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setLastError(null);
    }
}
