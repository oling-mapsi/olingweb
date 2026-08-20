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
    ) {
    }
}
