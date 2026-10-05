<?php

namespace App\Service\Growth;

use App\Dto\GrowthNewsInput;
use App\Entity\GrowthPublication;
use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use App\Service\GrowthPublishingService;

class OlingSitePublisher implements GrowthPublisherInterface
{
    public function __construct(private readonly GrowthPublishingService $publishingService) {}

    public function supports(GrowthDestination $destination): bool
    {
        return $destination === GrowthDestination::OLING_PUBLIC;
    }

    public function publish(GrowthPublication $publication): void
    {
        $content = $publication->getContent();
        $campaign = $publication->getCampaign();
        if ($content === null || $campaign === null) {
            throw new \RuntimeException('Publication is incomplete.');
        }

        $externalId = 'oling-growth-campaign-'.$campaign->getId();
        $input = GrowthNewsInput::fromArray([
            'external_id' => $externalId,
            'title' => $content->getTitle(),
            'slug' => $content->getSlug(),
            'excerpt' => $content->getExcerpt(),
            'content_html' => $content->getContentHtml(),
            'meta_title' => $content->getMetaTitle(),
            'meta_description' => $content->getMetaDescription(),
            'canonical_url' => 'https://oling.fr/ressources/'.$content->getSlug(),
            'featured_image' => $content->getFeaturedImage(),
            'categories' => $content->getCategories(),
            'tags' => $content->getTags(),
            'publication_date' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'status' => 'draft',
            'author_display_name' => $content->getAuthorDisplayName(),
            'source_campaign_id' => (string) $campaign->getId(),
        ]);

        $this->publishingService->createOrUpdateDraft($input);
        $this->publishingService->publish($externalId);

        $publication
            ->setStatus(GrowthPublicationStatus::PUBLISHED)
            ->setExternalIdentifier($externalId)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setLastError(null);
    }
}
