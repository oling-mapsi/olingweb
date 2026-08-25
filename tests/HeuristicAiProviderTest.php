<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\Ai\HeuristicAiProvider;
use App\Service\Chat\ChatQualificationService;
use PHPUnit\Framework\TestCase;

class HeuristicAiProviderTest extends TestCase
{
    public function testShortAmoaIso27001QueryDoesNotEchoInternalContentTitles(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('amoa iso27001')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [[
            'title' => 'AMOA ERP – MARTINI : Accompte démarrage Mission 20%',
            'url' => '/projets',
            'text' => 'Contenu interne non pertinent pour une réponse visiteur.',
            'type' => 'cas_client',
        ]], $qualification);

        self::assertStringNotContainsString('Je pense notamment à', $decision->reply);
        self::assertStringContainsString('AMOA', $decision->reply);
        self::assertStringContainsString('ISO 27001', $decision->reply);
    }

    public function testProviderKeepsQualificationQuestionWhenNeedIsSufficientButLeadIsNotRequestedAtProviderLevel(): void
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

        self::assertFalse($decision->requestLead);
        self::assertStringContainsString('OLING intervient sur le cadrage et la sécurisation des projets ERP', $decision->reply);
    }

    public function testProviderRefusesNamedClientDisclosure(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'Donnez-moi vos principaux clients.', [], []);

        self::assertStringContainsString('Je ne cite pas les noms de clients', $decision->reply);
        self::assertFalse($decision->requestLead);
    }

    public function testProviderPrioritizesAnonymizedReferencesForMixedSectorQuestion(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('projet SI client et facturation avez vous des références eaux et assainissement ?')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $qualificationService = new ChatQualificationService();
        $qualification = $qualificationService->qualify($conversation);
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision($conversation, $message->getContent(), [[
            'title' => 'Référence Eau et assainissement - AMOA progiciel',
            'url' => '/projets',
            'text' => 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.',
            'type' => 'reference',
        ]], $qualification);

        self::assertStringContainsString('références anonymisées', $decision->reply);
        self::assertStringContainsString('eau et assainissement', mb_strtolower($decision->reply));
        self::assertStringContainsString('AMOA progiciel', $decision->reply);
    }

    public function testProviderHandlesShortSectorFollowUpWithReferenceContext(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'eaux et assainissement ?', [[
            'title' => 'Référence Eau et assainissement - AMOA progiciel',
            'url' => '/projets',
            'text' => 'Mission AMOA progiciel en eau et assainissement avec cadrage, consultation, reprise de données et déploiement.',
            'type' => 'reference',
        ]], []);

        self::assertStringContainsString('références anonymisées', $decision->reply);
        self::assertStringContainsString('eau et assainissement', mb_strtolower($decision->reply));
    }

    public function testProviderReturnsDirectContactDetailsForPhoneQuestion(): void
    {
        $qualificationService = new ChatQualificationService();
        $provider = new HeuristicAiProvider($qualificationService);

        $decision = $provider->generateDecision(new ChatConversation(), 'votre numéro de téléphone ?', [], []);

        self::assertStringContainsString('01 89 70 15 60', $decision->reply);
        self::assertStringContainsString('contact@oling.fr', $decision->reply);
    }
}
