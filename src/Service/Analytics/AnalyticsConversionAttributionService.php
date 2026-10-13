<?php

namespace App\Service\Analytics;

use App\Entity\AnalyticsConversionAttribution;
use App\Entity\AnalyticsEvent;
use App\Repository\AnalyticsEventRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;

class AnalyticsConversionAttributionService
{
    public function __construct(
        private readonly AnalyticsEventRepository $events,
        private readonly EntityManagerInterface $entityManager,
        private readonly AnalyticsPathNormalizer $pathNormalizer
    ) {
    }

    public function attribute(string $type, string $reference, mixed $sessionId, ?string $conversionPage = null, ?\DateTimeImmutable $convertedAt = null): bool
    {
        $sessionId = $this->cleanToken($sessionId, 64);
        $type = $this->cleanToken($type, 32);
        $reference = $this->cleanToken($reference, 80);
        if ($sessionId === '' || $type === '' || $reference === '') {
            return false;
        }

        $convertedAt ??= new \DateTimeImmutable();
        $events = $this->events->sessionEvents($sessionId, $convertedAt->modify('-30 days'), $convertedAt->modify('+1 minute'));
        if ($events === []) {
            return false;
        }

        $pageEvents = array_values(array_filter($events, static fn (AnalyticsEvent $event): bool => $event->getEventName() === 'page_view'));
        $first = $pageEvents[0] ?? $events[0];
        $last = $pageEvents !== [] ? $pageEvents[array_key_last($pageEvents)] : $events[array_key_last($events)];
        $pages = [];
        foreach ($pageEvents as $event) {
            $pages[] = $event->getPagePath();
        }
        $pages = array_values(array_unique(array_slice($pages, 0, 12)));

        $attribution = (new AnalyticsConversionAttribution())
            ->setConversionType($type)
            ->setConversionReference($reference)
            ->setSessionId($sessionId)
            ->setEntryPage($first->getPagePath())
            ->setConversionPage($this->pathNormalizer->normalize($conversionPage ?: $last->getPagePath()))
            ->setSource($first->getSource())
            ->setVisitedPages($pages)
            ->setConvertedAt($convertedAt);

        $this->entityManager->persist($attribution);
        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $this->entityManager->clear();
            return false;
        }

        return true;
    }

    private function cleanToken(mixed $value, int $max): string
    {
        if (!is_scalar($value)) {
            return '';
        }

        $value = preg_replace('/[^a-zA-Z0-9_.:-]/', '', (string) $value) ?? '';

        return mb_substr($value, 0, $max);
    }
}
