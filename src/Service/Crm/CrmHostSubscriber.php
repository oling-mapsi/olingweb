<?php
namespace App\Service\Crm;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class CrmHostSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array { return [RequestEvent::class => ['onRequest', 64]]; }

    public function onRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) { return; }
        $request = $event->getRequest();
        if (!in_array($request->getHost(), ['crm.oling.fr', 'crm.localhost'], true)) { return; }
        // Session listener has attached the session, but authentication has not started it.
        if ($request->hasSession() && !$request->getSession()->isStarted()) {
            $request->getSession()->setName('OLINGCRMSESSID');
            $request->getSession()->setId($request->cookies->get('OLINGCRMSESSID', ''));
        }
        $path = $request->getPathInfo();
        if (in_array($path, ['/', '/admin', '/admin/'], true)) {
            $event->setResponse(new RedirectResponse('/admin/crm'));
        } elseif ($path === '/login') {
            $event->setResponse(new RedirectResponse('/crm/login'));
        } elseif (!preg_match('~^/(?:admin/crm(?:/|$)|crm/)~', $path)) {
            $event->setResponse(new Response('Page indisponible dans le CRM.', 404));
        }
    }
}
