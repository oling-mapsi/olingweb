<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Repository\ChatConversationRepository;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatConversationManager;
use App\Service\Chat\ChatLeadMailer;
use App\Service\Chat\ChatQualificationService;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\ChatSummaryService;
use App\Service\Chat\PublicContentCatalog;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ChatConversationManagerTest extends TestCase
{
    public function testContactOfferMessageSerializesLeadActionForWidget(): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('assistant')
            ->setContent('Je vous propose un premier échange avec un consultant OLING afin d’évaluer votre périmètre.')
            ->setMessageType('contact_offer')
            ->setProvider('openai')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $serialized = $this->manager()->serializeConversation($conversation);
        $actions = $serialized['messages'][0]['actions'];

        self::assertContains('open_lead_form', array_column($actions, 'type'));
        self::assertContains('Être recontacté par OLING', array_column($actions, 'label'));
    }

    public function testProposalRequestMessageSerializesDedicatedLeadAction(): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('assistant')
            ->setContent('OLING peut préparer une proposition adaptée à votre demande AMOA ERP.')
            ->setMessageType('proposal_request')
            ->setProvider('openai')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $actions = $this->manager()->serializeConversation($conversation)['messages'][0]['actions'];

        self::assertContains('open_lead_form', array_column($actions, 'type'));
        self::assertContains('Recevoir une proposition OLING', array_column($actions, 'label'));
    }

    public function testTechnicalUnavailableProposalMessageSerializesFallbackLeadAction(): void
    {
        $conversation = new ChatConversation();
        $conversation->addMessage((new ChatMessage())
            ->setRole('assistant')
            ->setContent('Je rencontre momentanément une difficulté technique. Un consultant OLING pourra examiner votre besoin et préparer une proposition adaptée.')
            ->setMessageType('technical_unavailable')
            ->setProvider('llm_unavailable')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        $actions = $this->manager()->serializeConversation($conversation)['messages'][0]['actions'];

        self::assertContains('open_lead_form', array_column($actions, 'type'));
        self::assertContains('Transmettre ma demande de proposition à OLING', array_column($actions, 'label'));
    }

    private function manager(): ChatConversationManager
    {
        $catalog = $this->createMock(PublicContentCatalog::class);
        $catalog->method('findCardsByUrls')->willReturn([]);
        $contentProvider = new AiConsultantContentProvider(dirname(__DIR__));

        return new ChatConversationManager(
            $this->createMock(EntityManagerInterface::class),
            $this->createMock(ChatConversationRepository::class),
            $this->createMock(ChatResponder::class),
            new ChatQualificationService(),
            new ChatSummaryService($contentProvider),
            $this->createMock(ChatLeadMailer::class),
            $catalog,
            $contentProvider
        );
    }
}
