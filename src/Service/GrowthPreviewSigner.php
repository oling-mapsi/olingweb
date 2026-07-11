<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

class GrowthPreviewSigner
{
    public function __construct(
        #[Autowire('%env(string:GROWTH_PREVIEW_SECRET)%')]
        private readonly string $secret,
        #[Autowire('%env(int:GROWTH_PREVIEW_TTL)%')]
        private readonly int $ttl
    ) {
    }

    public function buildSignature(string $externalId, int $revisionId, int $expires): string
    {
        return hash_hmac('sha256', $externalId.'|'.$revisionId.'|'.$expires, $this->secret);
    }

    public function generateParameters(string $externalId, int $revisionId): array
    {
        $expires = time() + $this->ttl;

        return [
            'expires' => $expires,
            'signature' => $this->buildSignature($externalId, $revisionId, $expires),
        ];
    }

    public function isValid(string $externalId, int $revisionId, int $expires, string $signature): bool
    {
        if ($expires < time()) {
            return false;
        }

        return hash_equals($this->buildSignature($externalId, $revisionId, $expires), $signature);
    }
}
