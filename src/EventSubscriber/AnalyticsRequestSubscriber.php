<?php

namespace App\EventSubscriber;

use App\Service\Analytics\InternalAnalyticsExclusionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class AnalyticsRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly InternalAnalyticsExclusionService $exclusionService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::REQUEST => ['onKernelRequest', 16]];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $request->attributes->set('analytics_internal_excluded', $this->exclusionService->isExcluded($request));
    }
}
