<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\KernelResponseEvent;

class XRobotsTagListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelResponseEvent::class => 'onKernelResponse',
        ];
    }

    public function onKernelResponse(KernelResponseEvent $event): void
    {
        $response = $event->getResponse();
        $response->headers->set('X-Robots-Tag', 'noindex');
    }
}
