<?php

namespace App\Service\I18n;

final class AiTranslationResult
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(
        public readonly array $payload,
        public readonly string $provider,
        public readonly ?string $model = null,
    ) {
    }
}
