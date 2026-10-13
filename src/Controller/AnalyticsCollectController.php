<?php

namespace App\Controller;

use App\Service\Analytics\AnalyticsCollector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AnalyticsCollectController extends AbstractController
{
    #[Route('/analytics/collect', name: 'analytics_collect', methods: ['POST'])]
    public function collect(Request $request, AnalyticsCollector $collector): JsonResponse
    {
        if ((int) $request->headers->get('Content-Length', 0) > 8192) {
            return $this->json(['success' => false], Response::HTTP_REQUEST_ENTITY_TOO_LARGE);
        }

        try {
            $payload = $request->toArray();
        } catch (\JsonException) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['success' => $collector->collect($payload, $request)]);
    }
}
