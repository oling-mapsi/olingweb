<?php

namespace App\Service\Chat\Ai;

use App\Entity\ChatConversation;

interface AiProviderInterface
{
    public function getName(): string;

    public function isAvailable(): bool;

    /**
     * @param array<int, array{title:string,url:string,text:string,type:string}> $documents
     * @param array<string, string|null> $qualification
     */
    public function generateDecision(
        ChatConversation $conversation,
        string $visitorMessage,
        array $documents,
        array $qualification
    ): AiDecision;
}
