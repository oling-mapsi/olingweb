<?php

namespace App\Service\ErpQuestionnaire;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactory;

class ErpQuestionnaireRateLimitGuard
{
    public function __construct(private readonly RateLimiterFactory $erpQuestionnaireLimiter)
    {
    }

    public function isAccepted(Request $request): bool
    {
        $key = $request->getClientIp() ?: 'anonymous';

        return $this->erpQuestionnaireLimiter->create($key)->consume()->isAccepted();
    }
}
