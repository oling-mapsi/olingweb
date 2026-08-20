<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\Ai\HeuristicAiProvider;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;

class HeuristicAiProviderTest extends TestCase
{
    public function testProviderRequestsLeadWhenQualificationIsSufficient(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Nous sommes une PME de 120 personnes avec un projet ERP bloque et un besoin de cadrage rapide ce trimestre.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [], $qualification);

        self::assertTrue($decision->requestLead);
        self::assertStringContainsString('coordonnées', $decision->reply);
    }
}
