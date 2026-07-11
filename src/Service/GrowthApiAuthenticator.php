<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class GrowthApiAuthenticator
{
    public function __construct(
        #[Autowire('%env(string:GROWTH_API_TOKEN)%')]
        private readonly string $token
    ) {
    }

    public function assertAuthorized(Request $request): void
    {
        $header = (string) $request->headers->get('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new AccessDeniedHttpException('Missing bearer token.');
        }

        $providedToken = trim(substr($header, 7));
        if ($providedToken === '' || !hash_equals($this->token, $providedToken)) {
            throw new AccessDeniedHttpException('Invalid bearer token.');
        }
    }
}
