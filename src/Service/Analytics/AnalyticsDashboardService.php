<?php

namespace App\Service\Analytics;

use App\Repository\AnalyticsEventRepository;
use App\Repository\AnalyticsConversionAttributionRepository;
use App\Repository\ChatConversationRepository;
use App\Repository\ErpQuestionnaireSubmissionRepository;
use App\Repository\MessagerieRepository;

class AnalyticsDashboardService
{
    public function __construct(
        private readonly AnalyticsEventRepository $events,
        private readonly AnalyticsConversionAttributionRepository $attributions,
        private readonly ChatConversationRepository $chatConversations,
        private readonly MessagerieRepository $messages,
        private readonly ErpQuestionnaireSubmissionRepository $erpSubmissions,
        private readonly AnalyticsPathNormalizer $pathNormalizer,
        private readonly Ga4DataApiClient $ga4
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function build(\DateTimeImmutable $from, \DateTimeImmutable $to): array
    {
        $sessions = $this->events->countDistinctSessions($from, $to);
        $pageViews = $this->events->countEvents('page_view', $from, $to);
        $contactCount = method_exists($this->messages, 'countCreatedBetween') ? $this->messages->countCreatedBetween($from, $to) : $this->events->countEvents('contact_confirmed', $from, $to);
        $chatLeads = $this->chatConversations->countSubmittedBetween($from, $to);
        $erpCount = $this->erpSubmissions->countCreatedBetween($from, $to);
        $prospects = $contactCount + $chatLeads + $erpCount;
        $attributableConversions = $this->attributions->countBetween($from, $to);

        $attributionByEntryAndSource = $this->attributions->byEntryAndSource($from, $to);
        $conversionsByEntry = [];
        foreach ($attributionByEntryAndSource as $row) {
            $entryPage = (string) $row['entryPage'];
            $conversionsByEntry[$entryPage] = ($conversionsByEntry[$entryPage] ?? 0) + (int) $row['conversions'];
        }

        $pages = array_map(function (array $row) use ($conversionsByEntry): array {
            $page = (string) $row['page'];
            return [
                ...$row,
                'family' => $this->pathNormalizer->family($page),
                'entries' => 0,
                'exits' => 0,
                'conversions' => $conversionsByEntry[$page] ?? 0,
                'engagedTime' => 'n/d',
            ];
        }, $this->events->topPages($from, $to));

        $dailyVisits = $this->events->dailyVisits($from, $to);
        $ga4Summary = $this->formatGa4Summary($this->ga4->summary($from, $to));

        return [
            'period' => ['from' => $from, 'to' => $to],
            'overview' => [
                'sessions' => $sessions,
                'pageViews' => $pageViews,
                'prospects' => $prospects,
                'attributableConversions' => $attributableConversions,
                'conversionRate' => $sessions > 0 && $attributableConversions > 0 ? round(($attributableConversions / $sessions) * 100, 1) : null,
            ],
            'definitions' => [
                'sessions' => 'Sessions consenties issues du collecteur first-party OLING.',
                'pageViews' => 'Evénements page_view consentis issus du collecteur first-party OLING.',
                'prospects' => 'Prospects confirmés issus des tables métier : contact, chat IA, questionnaires ERP.',
                'conversionRate' => 'Calculé uniquement avec les conversions first-party rattachées à une session collectée.',
            ],
            'pages' => $pages,
            'sources' => $this->events->sources($from, $to),
            'transitions' => $this->events->transitions($from, $to),
            'attributionByEntryAndSource' => $attributionByEntryAndSource,
            'attributedConversions' => $this->attributions->recent($from, $to),
            'dailyVisits' => $dailyVisits,
            'maxDailyVisits' => max([1, ...array_column($dailyVisits, 'visits')]),
            'ga4' => $ga4Summary,
            'conversions' => [
                'contact' => $contactCount,
                'chat' => $chatLeads,
                'erp' => $erpCount,
                'phone' => $this->events->countEvents('phone_click', $from, $to),
                'email' => $this->events->countEvents('email_click', $from, $to),
                'downloads' => $this->events->countEvents('resource_download', $from, $to),
            ],
            'quality' => [
                'ga4' => $ga4Summary['error'] ? ['configured' => false, 'label' => $ga4Summary['error']] : $this->ga4->status(),
                'collector' => 'Collecteur interne actif',
                'unknownSources' => 0,
            ],
        ];
    }

    /**
     * @param array<string, mixed> $summary
     * @return array<string, mixed>
     */
    private function formatGa4Summary(array $summary): array
    {
        if (!empty($summary['error'])) {
            return [
                'configured' => !empty($summary['configured']),
                'error' => (string) $summary['error'],
                'sessions' => 0,
                'pageViews' => 0,
                'engagedSessions' => 0,
                'pages' => [],
                'sources' => [],
                'daily' => [],
            ];
        }

        $totals = $summary['totals']['rows'][0]['metricValues'] ?? [];

        return [
            'configured' => true,
            'error' => null,
            'sessions' => $this->metric($totals, 0),
            'pageViews' => $this->metric($totals, 1),
            'engagedSessions' => $this->metric($totals, 2),
            'pages' => array_map(function (array $row): array {
                return [
                    'page' => (string) ($row['dimensionValues'][0]['value'] ?? '/'),
                    'views' => $this->metric($row['metricValues'] ?? [], 0),
                    'sessions' => $this->metric($row['metricValues'] ?? [], 1),
                ];
            }, array_slice($summary['pages']['rows'] ?? [], 0, 10)),
            'sources' => array_map(function (array $row): array {
                $source = (string) ($row['dimensionValues'][0]['value'] ?? '(not set)');
                $medium = (string) ($row['dimensionValues'][1]['value'] ?? '(not set)');

                return [
                    'source' => trim($source.' / '.$medium),
                    'sessions' => $this->metric($row['metricValues'] ?? [], 0),
                    'pageViews' => $this->metric($row['metricValues'] ?? [], 1),
                ];
            }, array_slice($summary['sources']['rows'] ?? [], 0, 10)),
            'daily' => array_map(function (array $row): array {
                return [
                    'date' => (string) ($row['dimensionValues'][0]['value'] ?? ''),
                    'sessions' => $this->metric($row['metricValues'] ?? [], 0),
                    'pageViews' => $this->metric($row['metricValues'] ?? [], 1),
                ];
            }, $summary['daily']['rows'] ?? []),
        ];
    }

    /**
     * @param array<int, array{value?: string}> $values
     */
    private function metric(array $values, int $index): int
    {
        return (int) ($values[$index]['value'] ?? 0);
    }
}
