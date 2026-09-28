<?php

namespace App\Service\Chat;

final class ChatReply
{
    /**
     * @param array<string, string|null> $qualification
     * @param string[] $sources
     */
    public function __construct(
        public readonly string $content,
        public readonly bool $requestLead = false,
        public readonly array $sources = [],
        public readonly array $qualification = [],
        public readonly ?string $provider = null,
        public readonly string $messageType = 'question',
        public readonly ?string $model = null,
        public readonly bool $fallbackUsed = false,
        public readonly ?string $ownerUrl = null,
        public readonly array $selectedDocuments = [],
        public readonly ?int $latencyMs = null,
        public readonly ?int $inputTokens = null,
        public readonly ?int $outputTokens = null,
        public readonly ?string $errorCode = null,
        public readonly ?string $requestId = null,
    ) {
    }
}
