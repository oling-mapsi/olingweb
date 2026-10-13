<?php

namespace App\EventSubscriber;

use App\Service\Analytics\InternalAnalyticsExclusionService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class AdminAnalyticsExclusionSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly InternalAnalyticsExclusionService $exclusionService)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [LoginSuccessEvent::class => 'onLoginSuccess'];
    }

    public function onLoginSuccess(LoginSuccessEvent $event): void
    {
        $user = $event->getUser();
        if (!method_exists($user, 'getRoles') || !in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return;
        }

        $event->getResponse()?->headers->setCookie($this->exclusionService->createCookie());
    }
}
