<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;

class ChatQualificationServiceTest extends TestCase
{
    public function testQualificationCapturesStructuredSignals(): void
    {
        $conversation = new ChatConversation();

        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Nous sommes une PME de 120 personnes avec un projet ERP bloque et un besoin de cadrage rapide ce trimestre.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());

        $conversation->addMessage($message);

        $service = new ChatQualificationService();
        $qualification = $service->qualify($conversation);

        self::assertSame('amoa_erp', $qualification['primary_need']);
        self::assertSame('short_term', $qualification['urgency_level']);
        self::assertSame('bloque', $qualification['maturity_level']);
        self::assertSame('pme', $qualification['organization_type']);
        self::assertSame('50_249', $qualification['organization_size']);
        self::assertSame('cadrage', $qualification['commercial_intent']);
        self::assertSame('high', $qualification['potential_value']);
        self::assertFalse($service->isTooVague($qualification));
        self::assertTrue($service->isReadyForLead($qualification, $conversation));
    }

    public function testModelHintsFillMissingFields(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Nous avons un sujet data et automatisation.')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());

        $conversation->addMessage($message);

        $service = new ChatQualificationService();
        $qualification = $service->qualify($conversation, [
            'urgency_level' => 'planned',
            'organization_type' => 'eti',
            'commercial_intent' => 'diagnostic',
        ]);

        self::assertSame('ia_data_automatisation', $qualification['primary_need']);
        self::assertSame('planned', $qualification['urgency_level']);
        self::assertSame('eti', $qualification['organization_type']);
        self::assertSame('diagnostic', $qualification['commercial_intent']);
    }
}
