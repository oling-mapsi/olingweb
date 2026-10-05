<?php

namespace App\Service\Growth;

use App\Entity\GrowthAuditEvent;
use App\Entity\GrowthCampaign;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class GrowthAuditLogger
{
    public function __construct(private readonly EntityManagerInterface $entityManager) {}

    /**
     * @param array<string, scalar|null> $context
     */
    public function log(string $eventName, ?GrowthCampaign $campaign = null, ?UserInterface $actor = null, array $context = []): void
    {
        unset($context['token'], $context['api_key'], $context['jwt'], $context['secret']);

        $event = (new GrowthAuditEvent())
            ->setEventName($eventName)
            ->setCampaign($campaign)
            ->setActor($actor?->getUserIdentifier())
            ->setContext($context);

        $this->entityManager->persist($event);
    }
}
