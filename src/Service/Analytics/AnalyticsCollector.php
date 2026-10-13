<?php

namespace App\Service\Analytics;

use App\Entity\AnalyticsEvent;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class AnalyticsCollector
{
    private const ALLOWED_EVENTS = [
        'page_view',
        'cta_click',
        'phone_click',
        'email_click',
        'contact_confirmed',
        'chat_opened',
        'chat_first_message',
        'chat_lead_confirmed',
        'erp_questionnaire_confirmed',
        'resource_download',
        'diagnostic_completed',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AnalyticsSourceClassifier $sourceClassifier,
        private readonly AnalyticsPathNormalizer $pathNormalizer,
        private readonly InternalAnalyticsExclusionService $exclusionService
    ) {
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function collect(array $payload, Request $request): bool
    {
        if ($this->exclusionService->isExcluded($request) || $this->isInternalPath($request->getPathInfo()) || $this->isBot($request)) {
            return false;
        }

        $eventName = $this->cleanToken($payload['eventName'] ?? '', 64);
        $eventId = $this->cleanToken($payload['eventId'] ?? '', 64);
        $sessionId = $this->cleanToken($payload['sessionId'] ?? '', 64);
        if (!in_array($eventName, self::ALLOWED_EVENTS, true) || $eventId === '' || $sessionId === '') {
            return false;
        }

        $utmSource = $this->cleanNullable($payload['utmSource'] ?? null, 100);
        $referrer = $this->cleanNullable($payload['referrer'] ?? null, 500);
        $event = (new AnalyticsEvent())
            ->setEventName($eventName)
            ->setEventId($eventId)
            ->setSessionId($sessionId)
            ->setPagePath($this->pathNormalizer->normalize((string) ($payload['pagePath'] ?? '/')))
            ->setPreviousPagePath($this->nullablePath($payload['previousPagePath'] ?? null))
            ->setSource($this->sourceClassifier->classify($utmSource, $referrer))
            ->setUtmSource($utmSource)
            ->setUtmMedium($this->cleanNullable($payload['utmMedium'] ?? null, 100))
            ->setUtmCampaign($this->cleanNullable($payload['utmCampaign'] ?? null, 150))
            ->setLocale($this->cleanNullable($payload['locale'] ?? null, 16));

        $this->entityManager->persist($event);
        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            $this->entityManager->clear();
            return false;
        }

        return true;
    }

    public function isInternalPath(string $path): bool
    {
        return str_starts_with($path, '/admin')
            || str_starts_with($path, '/login')
            || str_starts_with($path, '/logout')
            || str_starts_with($path, '/preview/')
            || str_starts_with($path, '/_');
    }

    private function nullablePath(mixed $value): ?string
    {
        $path = $this->pathNormalizer->normalize(is_string($value) ? $value : '');
        return $path === '/' ? null : $path;
    }

    private function cleanToken(mixed $value, int $max): string
    {
        $value = preg_replace('/[^a-zA-Z0-9_.:-]/', '', (string) $value) ?? '';
        return mb_substr($value, 0, $max);
    }

    private function cleanNullable(mixed $value, int $max): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }
        $value = trim(strip_tags((string) $value));
        if ($value === '') {
            return null;
        }
        return mb_substr($value, 0, $max);
    }

    private function isBot(Request $request): bool
    {
        $ua = mb_strtolower($request->headers->get('User-Agent', ''));
        return (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|pingdom|uptime/i', $ua);
    }
}
