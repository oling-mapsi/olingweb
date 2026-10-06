<?php

namespace App\Service\Growth;

use App\Dto\GrowthNewsInput;
use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Enum\GrowthDestination;
use App\Service\GrowthPreviewSigner;
use App\Service\GrowthPublishingService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GrowthPreviewBuilder
{
    public function __construct(
        private readonly GrowthPublishingService $olingPublishingService,
        private readonly GrowthPreviewSigner $previewSigner,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly HttpClientInterface $httpClient,
        private readonly ?string $mapsiBaseUrl,
        private readonly ?string $mapsiToken,
    ) {
    }

    public function build(GrowthCampaign $campaign, GrowthDestination $destination): array
    {
        $content = $campaign->getPrimaryContent();
        if (!$content instanceof GrowthContent) {
            throw new \RuntimeException('No Growth content is available for preview.');
        }

        return match ($destination) {
            GrowthDestination::OLING_PUBLIC => $this->olingPreview($campaign, $content),
            GrowthDestination::MAPSI_PUBLIC => $this->mapsiPreview($campaign, $content),
        };
    }

    private function olingPreview(GrowthCampaign $campaign, GrowthContent $content): array
    {
        $externalId = $this->externalId($campaign);
        $status = $this->olingPublishingService->createOrUpdateDraft($this->input($campaign, $content, 'https://oling.fr/ressources/'.$content->getSlug()));
        $revisionId = (int) ($status['draft_revision_number'] ?? 0);
        if ($revisionId <= 0) {
            $revisionId = (int) $this->olingPublishingService->buildPreviewPage($externalId)->getId();
        }

        $signature = $this->previewSigner->generateParameters($externalId, $revisionId);

        return [
            'mode' => 'signed',
            'url' => $this->urlGenerator->generate('growth_preview_article', [
                'externalId' => $externalId,
                'revisionId' => $revisionId,
                'expires' => $signature['expires'],
                'signature' => $signature['signature'],
            ], UrlGeneratorInterface::ABSOLUTE_URL),
            'expires_at' => gmdate(DATE_ATOM, $signature['expires']),
        ];
    }

    private function mapsiPreview(GrowthCampaign $campaign, GrowthContent $content): array
    {
        if (trim((string) $this->mapsiBaseUrl) === '' || trim((string) $this->mapsiToken) === '') {
            return [
                'mode' => 'internal',
                'url' => $this->urlGenerator->generate('admin_growth_campaign_internal_preview', ['id' => $campaign->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
                'expires_at' => null,
            ];
        }

        $externalId = $this->externalId($campaign);
        $baseUrl = rtrim((string) $this->mapsiBaseUrl, '/');
        $token = (string) $this->mapsiToken;
        $this->httpClient->request('POST', $baseUrl.'/api/growth/news', [
            'headers' => ['Authorization' => 'Bearer '.$token, 'Content-Type' => 'application/json'],
            'json' => $this->payload($campaign, $content, 'https://mapsi.fr/actualites/'.$content->getSlug()),
            'timeout' => 15,
        ])->getStatusCode();

        $response = $this->httpClient->request('POST', $baseUrl.'/api/growth/news/'.rawurlencode($externalId).'/preview-url', [
            'headers' => ['Authorization' => 'Bearer '.$token],
            'timeout' => 15,
        ])->toArray();

        return [
            'mode' => 'signed',
            'url' => (string) ($response['preview_url'] ?? ''),
            'expires_at' => $response['expires_at'] ?? null,
        ];
    }

    private function input(GrowthCampaign $campaign, GrowthContent $content, string $canonicalUrl): GrowthNewsInput
    {
        return GrowthNewsInput::fromArray($this->payload($campaign, $content, $canonicalUrl));
    }

    private function payload(GrowthCampaign $campaign, GrowthContent $content, string $canonicalUrl): array
    {
        return [
            'external_id' => $this->externalId($campaign),
            'title' => $content->getTitle(),
            'slug' => $content->getSlug(),
            'excerpt' => $content->getExcerpt(),
            'content_html' => $content->getContentHtml(),
            'meta_title' => $content->getMetaTitle(),
            'meta_description' => $content->getMetaDescription(),
            'canonical_url' => $canonicalUrl,
            'featured_image' => $content->getFeaturedImage(),
            'categories' => $content->getCategories(),
            'tags' => $content->getTags(),
            'publication_date' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'status' => 'draft',
            'author_display_name' => $content->getAuthorDisplayName(),
            'source_campaign_id' => (string) $campaign->getId(),
        ];
    }

    private function externalId(GrowthCampaign $campaign): string
    {
        return 'oling-growth-campaign-'.$campaign->getId();
    }
}
