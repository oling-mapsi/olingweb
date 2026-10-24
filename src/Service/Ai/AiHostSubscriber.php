<?php
namespace App\Service\Ai;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\{RedirectResponse,Response};
use Symfony\Component\HttpKernel\Event\{RequestEvent,ResponseEvent,ExceptionEvent};
final class AiHostSubscriber implements EventSubscriberInterface
{
    public static function hosts(string $host): bool {return in_array($host,['ai.oling.fr','ai.localhost'],true);}
    public static function getSubscribedEvents(): array {return [RequestEvent::class=>['request',64],ResponseEvent::class=>['response',-1100],ExceptionEvent::class=>['error',-20]];}
    public function request(RequestEvent $event): void
    {
        if(!$event->isMainRequest())return;$r=$event->getRequest();$path=$r->getPathInfo();$ai=self::hosts($r->getHost());
        $private=(bool)preg_match('~^/(?:admin/ai(?:/|$)|ai(?:/|$)|api/ai-relay(?:/|$)|api/ai/mcp$)~',$path);
        if(!$ai) {if($private)$event->setResponse(new Response('Introuvable',404));return;}
        if($r->hasSession()&&!$r->getSession()->isStarted()) {$r->getSession()->setName('OLINGAISESSID');$r->getSession()->setId($r->cookies->get('OLINGAISESSID',''));}
        if(in_array($path,['/','/admin','/admin/'],true))$event->setResponse(new RedirectResponse('/admin/ai'));
        elseif($path==='/login')$event->setResponse(new RedirectResponse('/ai/login'));
        elseif(!$private)$event->setResponse(new Response('Introuvable',404));
    }
    public function error(ExceptionEvent $event): void
    {
        if(!self::hosts($event->getRequest()->getHost()))return;
        $error=$event->getThrowable();
        $status=$error instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface?$error->getStatusCode():500;
        $event->setResponse(new Response($status===403?'Accès refusé.':($status===404?'Introuvable.':'Une erreur est survenue.'),$status));
    }
    public function response(ResponseEvent $event): void
    {
        if(!self::hosts($event->getRequest()->getHost()))return;
        $headers=$event->getResponse()->headers;
        $headers->set('Cache-Control','private, no-store');$headers->set('X-Robots-Tag','noindex, nofollow');
        $headers->set('X-Content-Type-Options','nosniff');$headers->set('Referrer-Policy','no-referrer');
        foreach($headers->getCookies() as $cookie)if($cookie->getName()==='OLINGAISESSID')$headers->setCookie($cookie->withDomain(null)->withHttpOnly(true)->withSameSite('lax')->withSecure($event->getRequest()->isSecure()));
    }
}
