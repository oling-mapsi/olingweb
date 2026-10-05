<?php

namespace App\Service\Growth;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Entity\GrowthPublication;
use App\Enum\GrowthCampaignStatus;
use App\Enum\GrowthContentStatus;
use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GrowthWorkflow
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly GrowthContentGeneratorInterface $generator,
        private readonly GrowthPublisherRegistry $publishers,
        private readonly GrowthAuditLogger $auditLogger,
    ) {
    }

    public function createCampaign(GrowthCampaign $campaign, ?UserInterface $actor): void
    {
        $campaign->setCreatedBy($actor?->getUserIdentifier());
        $this->entityManager->persist($campaign);
        $this->auditLogger->log('campaign_created', $campaign, $actor);
        $this->entityManager->flush();
    }

    public function generate(GrowthCampaign $campaign, ?UserInterface $actor): GrowthContent
    {
        $content = $this->generator->generate($campaign);
        $campaign->addContent($content);
        $campaign->setStatus(GrowthCampaignStatus::DRAFT);
        $this->entityManager->persist($content);
        $this->auditLogger->log('content_generated', $campaign, $actor, ['content_id' => $content->getId()]);
        $this->entityManager->flush();

        return $content;
    }

    public function review(GrowthCampaign $campaign, GrowthContent $content, ?UserInterface $actor): void
    {
        $content->setStatus(GrowthContentStatus::REVIEWED);
        $campaign->setStatus(GrowthCampaignStatus::IN_REVIEW);
        $this->auditLogger->log('content_reviewed', $campaign, $actor, ['content_id' => $content->getId()]);
        $this->entityManager->flush();
    }

    public function approve(GrowthCampaign $campaign, GrowthContent $content, ?UserInterface $actor): void
    {
        $content->setStatus(GrowthContentStatus::APPROVED);
        $campaign->setStatus(GrowthCampaignStatus::APPROVED);
        $this->auditLogger->log('content_approved', $campaign, $actor, ['content_id' => $content->getId()]);
        $this->entityManager->flush();
    }

    public function publish(GrowthCampaign $campaign, GrowthDestination $destination, ?UserInterface $actor): GrowthPublication
    {
        $content = $campaign->getPrimaryContent();
        if (!$content instanceof GrowthContent || $content->getStatus() !== GrowthContentStatus::APPROVED) {
            throw new \RuntimeException('Only approved Growth content can be published.');
        }

        $publication = (new GrowthPublication())
            ->setCampaign($campaign)
            ->setContent($content)
            ->setDestination($destination)
            ->setStatus(GrowthPublicationStatus::PENDING);

        $this->entityManager->persist($publication);
        $this->auditLogger->log('publication_requested', $campaign, $actor, ['destination' => $destination->value]);

        try {
            $this->publishers->forDestination($destination)->publish($publication);
            if ($publication->getStatus() === GrowthPublicationStatus::PUBLISHED) {
                $campaign->setStatus(GrowthCampaignStatus::PUBLISHED);
                $this->auditLogger->log('publication_succeeded', $campaign, $actor, ['destination' => $destination->value]);
            }
        } catch (\Throwable $exception) {
            $publication->setStatus(GrowthPublicationStatus::FAILED)->setLastError($exception->getMessage());
            $this->auditLogger->log('publication_failed', $campaign, $actor, ['destination' => $destination->value]);
        }

        $this->entityManager->flush();

        return $publication;
    }

    public function delete(GrowthCampaign $campaign, ?UserInterface $actor): void
    {
        $this->auditLogger->log('campaign_deleted', $campaign, $actor, ['campaign_title' => $campaign->getTitle()]);
        $this->entityManager->remove($campaign);
        $this->entityManager->flush();
    }
}
