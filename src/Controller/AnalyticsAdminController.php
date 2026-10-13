<?php

namespace App\Controller;

use App\Service\Analytics\AnalyticsDashboardService;
use App\Service\Analytics\InternalAnalyticsExclusionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/analytics')]
class AnalyticsAdminController extends AbstractController
{
    #[Route('', name: 'admin_analytics_index', methods: ['GET'])]
    #[Route('/', name: 'admin_analytics_index_slash', methods: ['GET'])]
    public function index(
        Request $request,
        AnalyticsDashboardService $dashboard,
        InternalAnalyticsExclusionService $exclusionService
    ): Response {
        [$from, $to, $period] = $this->period($request);

        return $this->render('admin/analytics/index.html.twig', [
            'dashboard' => $dashboard->build($from, $to),
            'period' => $period,
            'isExcluded' => $exclusionService->isExcluded($request),
        ]);
    }

    #[Route('/exclude-device', name: 'admin_analytics_exclude_device', methods: ['POST'])]
    public function exclude(Request $request, InternalAnalyticsExclusionService $exclusionService): Response
    {
        if (!$this->isCsrfTokenValid('analytics_device_exclusion', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $response = $this->redirectToRoute('admin_analytics_index');
        $response->headers->setCookie($exclusionService->createCookie());
        return $response;
    }

    #[Route('/include-device', name: 'admin_analytics_include_device', methods: ['POST'])]
    public function include(Request $request, InternalAnalyticsExclusionService $exclusionService): Response
    {
        if (!$this->isCsrfTokenValid('analytics_device_exclusion', (string) $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        $response = $this->redirectToRoute('admin_analytics_index');
        $response->headers->setCookie($exclusionService->clearCookie());
        return $response;
    }

    #[Route('/export.csv', name: 'admin_analytics_export', methods: ['GET'])]
    public function export(Request $request, AnalyticsDashboardService $dashboard): Response
    {
        [$from, $to] = $this->period($request);
        $data = $dashboard->build($from, $to);
        $lines = ["type;label;value"];
        foreach ($data['overview'] as $label => $value) {
            $lines[] = sprintf('overview;%s;%s', $label, $value ?? 'n/d');
        }
        foreach (['sessions', 'pageViews', 'engagedSessions'] as $label) {
            $lines[] = sprintf('ga4;%s;%s', $label, $data['ga4'][$label] ?? 'n/d');
        }
        foreach ($data['sources'] as $row) {
            $lines[] = sprintf('source;%s;%s', $row['source'], $row['sessions']);
        }
        foreach ($data['attributionByEntryAndSource'] as $row) {
            $lines[] = sprintf('attribution;%s|%s;%s', $row['entryPage'], $row['source'], $row['conversions']);
        }

        return new Response(implode("\n", $lines)."\n", Response::HTTP_OK, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="oling-audience-parcours.csv"',
        ]);
    }

    /**
     * @return array{0: \DateTimeImmutable, 1: \DateTimeImmutable, 2?: string}
     */
    private function period(Request $request): array
    {
        $period = (string) $request->query->get('period', '30');
        $days = in_array($period, ['7', '30', '90'], true) ? (int) $period : 30;
        $to = new \DateTimeImmutable('tomorrow');
        $from = $to->modify(sprintf('-%d days', $days));

        return [$from, $to, (string) $days];
    }
}
