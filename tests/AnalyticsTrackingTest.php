<?php

namespace App\Tests;

use App\Service\Analytics\AnalyticsPathNormalizer;
use App\Service\Analytics\AnalyticsSourceClassifier;
use App\Service\Analytics\AnalyticsDashboardService;
use App\Service\Analytics\AnalyticsConversionAttributionService;
use App\Service\Analytics\Ga4DataApiClient;
use App\Service\Analytics\InternalAnalyticsExclusionService;
use App\Entity\AnalyticsConversionAttribution;
use App\Entity\AnalyticsEvent;
use App\Repository\AnalyticsEventRepository;
use App\Repository\AnalyticsConversionAttributionRepository;
use App\Repository\ChatConversationRepository;
use App\Repository\ErpQuestionnaireSubmissionRepository;
use App\Repository\MessagerieRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class AnalyticsTrackingTest extends TestCase
{
    public function testSourceClassifierRecognizesLinkedInAndAssistants(): void
    {
        $classifier = new AnalyticsSourceClassifier();

        self::assertSame('linkedin', $classifier->classify('linkedin', null));
        self::assertSame('chatgpt_openai', $classifier->classify(null, 'https://chatgpt.com/'));
        self::assertSame('claude_anthropic', $classifier->classify(null, 'https://claude.ai/'));
        self::assertSame('direct', $classifier->classify(null, null));
    }

    public function testInternalExclusionCookieIsSigned(): void
    {
        $service = new InternalAnalyticsExclusionService('test-secret');
        $cookie = $service->createCookie();
        $request = Request::create('/');
        $request->cookies->set(InternalAnalyticsExclusionService::COOKIE_NAME, $cookie->getValue());

        self::assertTrue($service->isExcluded($request));

        $request->cookies->set(InternalAnalyticsExclusionService::COOKIE_NAME, '1.invalid');
        self::assertFalse($service->isExcluded($request));
    }

    public function testPathNormalizerRemovesQueryAndTrailingSlash(): void
    {
        $normalizer = new AnalyticsPathNormalizer();

        self::assertSame('/business-apps/erp', $normalizer->normalize('/business-apps/erp/?utm_source=linkedin'));
        self::assertSame('AMOA ERP', $normalizer->family('/business-apps/erp'));
        self::assertSame('Accueil', $normalizer->family('/'));
    }

    public function testDashboardDoesNotComputeRateFromUnrelatedBusinessProspects(): void
    {
        $from = new \DateTimeImmutable('2026-10-01');
        $to = new \DateTimeImmutable('2026-10-08');

        $events = $this->createMock(AnalyticsEventRepository::class);
        $events->method('countDistinctSessions')->willReturn(1);
        $events->method('countEvents')->willReturnMap([
            ['page_view', $from, $to, 1],
            ['phone_click', $from, $to, 0],
            ['email_click', $from, $to, 0],
            ['resource_download', $from, $to, 0],
        ]);
        $events->method('countEventsIn')->willReturn(0);
        $events->method('topPages')->willReturn([['page' => '/', 'views' => 1, 'sessions' => 1]]);
        $events->method('sources')->willReturn([]);
        $events->method('transitions')->willReturn([]);
        $events->method('dailyVisits')->willReturn([['date' => '2026-10-01', 'visits' => 1]]);
        $attributions = $this->createMock(AnalyticsConversionAttributionRepository::class);
        $attributions->method('countBetween')->willReturn(0);
        $attributions->method('byEntryAndSource')->willReturn([]);
        $attributions->method('recent')->willReturn([]);

        $messages = $this->createMock(MessagerieRepository::class);
        $messages->method('countCreatedBetween')->willReturn(1);
        $chat = $this->createMock(ChatConversationRepository::class);
        $chat->method('countSubmittedBetween')->willReturn(1);
        $erp = $this->createMock(ErpQuestionnaireSubmissionRepository::class);
        $erp->method('countCreatedBetween')->willReturn(1);
        $ga4 = $this->createMock(Ga4DataApiClient::class);
        $ga4->method('status')->willReturn(['configured' => false, 'label' => 'Connexion GA4 à configurer']);

        $dashboard = new AnalyticsDashboardService($events, $attributions, $chat, $messages, $erp, new AnalyticsPathNormalizer(), $ga4);
        $data = $dashboard->build($from, $to);

        self::assertSame(3, $data['overview']['prospects']);
        self::assertSame(0, $data['overview']['attributableConversions']);
        self::assertNull($data['overview']['conversionRate']);
        self::assertSame('Accueil', $data['pages'][0]['family']);
    }

    public function testGa4ClientReportsMissingConfiguration(): void
    {
        $client = new Ga4DataApiClient(new ArrayAdapter(), new MockHttpClient(), null, null);

        self::assertFalse($client->status()['configured']);
        self::assertSame('Property GA4 à configurer', $client->summary(new \DateTimeImmutable('-7 days'), new \DateTimeImmutable())['error']);
    }

    public function testConversionAttributionUsesServerConfirmedSessionEvents(): void
    {
        $first = (new AnalyticsEvent())
            ->setEventId('event-1')
            ->setSessionId('session-1')
            ->setEventName('page_view')
            ->setPagePath('/entry')
            ->setSource('linkedin')
            ->setOccurredAt(new \DateTimeImmutable('2026-10-01 10:00:00'));
        $second = (new AnalyticsEvent())
            ->setEventId('event-2')
            ->setSessionId('session-1')
            ->setEventName('page_view')
            ->setPagePath('/contact')
            ->setSource('linkedin')
            ->setOccurredAt(new \DateTimeImmutable('2026-10-01 10:05:00'));

        $events = $this->createMock(AnalyticsEventRepository::class);
        $events->method('sessionEvents')->willReturn([$first, $second]);

        $persisted = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())
            ->method('persist')
            ->with(self::callback(function (AnalyticsConversionAttribution $attribution) use (&$persisted): bool {
                $persisted = $attribution;
                return true;
            }));
        $entityManager->expects(self::once())->method('flush');

        $service = new AnalyticsConversionAttributionService($events, $entityManager, new AnalyticsPathNormalizer());

        self::assertTrue($service->attribute('contact', '42', 'session-1', '/contact', new \DateTimeImmutable('2026-10-01 10:06:00')));
        self::assertInstanceOf(AnalyticsConversionAttribution::class, $persisted);
        self::assertSame('/entry', $persisted->getEntryPage());
        self::assertSame('/contact', $persisted->getConversionPage());
        self::assertSame('linkedin', $persisted->getSource());
        self::assertSame(['/entry', '/contact'], $persisted->getVisitedPages());
    }

    public function testGa4ClientRunsReportWithServiceAccount(): void
    {
        $key = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        self::assertNotFalse($key);
        openssl_pkey_export($key, $privateKey);
        $credentialsPath = tempnam(sys_get_temp_dir(), 'ga4-test-');
        file_put_contents($credentialsPath, json_encode([
            'client_email' => 'analytics@example.test',
            'private_key' => $privateKey,
        ], JSON_THROW_ON_ERROR));

        $requests = [];
        $httpClient = new MockHttpClient(function (string $method, string $url, array $options) use (&$requests): MockResponse {
            $requests[] = [$method, $url, $options];
            if (str_contains($url, 'oauth2.googleapis.com')) {
                return new MockResponse(json_encode(['access_token' => 'test-token'], JSON_THROW_ON_ERROR));
            }

            return new MockResponse(json_encode(['rows' => []], JSON_THROW_ON_ERROR));
        });

        $client = new Ga4DataApiClient(new ArrayAdapter(), $httpClient, '123456789', $credentialsPath);
        $report = $client->runReport(new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-08'), ['sessions'], ['date']);

        self::assertSame(['rows' => []], $report);
        self::assertCount(2, $requests);
        self::assertStringContainsString('properties/123456789:runReport', $requests[1][1]);

        @unlink($credentialsPath);
    }
}
