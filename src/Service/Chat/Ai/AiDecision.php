<?php

namespace App\Service\Chat\Ai;

final class AiDecision
{
    /**
     * @param array<string, string|null> $qualification
     * @param string[] $sources
     * @param string[] $missingFields
     */
    public function __construct(
        public readonly string $reply,
        public readonly bool $requestLead,
        public readonly array $qualification = [],
        public readonly array $sources = [],
        public readonly array $missingFields = [],
        public readonly ?float $confidence = null,
        public readonly ?string $provider = null,
        public readonly ?string $model = null,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
        public readonly ?string $requestId = null,
    ) {
    }
}
