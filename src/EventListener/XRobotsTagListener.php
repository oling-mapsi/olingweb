<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;

class XRobotsTagListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $route = (string) $request->attributes->get('_route', '');
        $path = $request->getPathInfo();

        $isPrivate = str_starts_with($path, '/admin')
            || in_array($route, ['login', 'logout'], true)
            || str_starts_with($path, '/login')
            || str_starts_with($path, '/logout');
        $isError = $response->getStatusCode() >= 400;

        if ($isPrivate || $isError) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');

            return;
        }

        $response->headers->set('X-Robots-Tag', 'index, follow, max-image-preview:large');
    }
}
