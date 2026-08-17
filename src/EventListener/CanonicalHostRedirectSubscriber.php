<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CanonicalHostRedirectSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $siteBaseUrl)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 200],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $currentHost = (string) $request->getHost();
        if ($currentHost === '' || $currentHost !== 'www.oling.fr') {
            return;
        }

        $targetBaseUrl = rtrim($this->siteBaseUrl, '/');
        $targetUrl = $targetBaseUrl . $request->getRequestUri();
        $currentUrl = $request->getSchemeAndHttpHost() . $request->getRequestUri();

        if ($targetUrl === $currentUrl) {
            return;
        }

        $event->setResponse(new RedirectResponse($targetUrl, RedirectResponse::HTTP_MOVED_PERMANENTLY));
    }
}
