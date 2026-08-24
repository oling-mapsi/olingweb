<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Repository\SitePageRepository;
use App\Repository\TeamRepository;
use App\Service\Chat\Ai\HeuristicAiProvider;
use App\Service\Chat\ChatQualificationService;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\PublicContentCatalog;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChatResponderTest extends TestCase
{
    public function testGreetingKeepsConversationalOpeningWithoutGenericScopeSentence(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('Bonjour')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'Bonjour');

        self::assertStringStartsWith('Bonjour.', $reply->content);
        self::assertStringNotContainsString('Je reste dans le périmètre public du site OLING.', $reply->content);
        self::assertSame([], $reply->sources);
    }

    public function testDirectContactQuestionReturnsPhoneAndEmailWithoutSources(): void
    {
        $conversation = new ChatConversation();
        $message = (new ChatMessage())
            ->setRole('visitor')
            ->setContent('votre numero de tel')
            ->setMessageType('answer')
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable());
        $conversation->addMessage($message);

        $reply = $this->buildResponder()->reply($conversation, 'votre numero de tel');

        self::assertStringContainsString('01 89 70 15 60', $reply->content);
        self::assertStringContainsString('contact@oling.fr', $reply->content);
        self::assertSame([], $reply->sources);
        self::assertTrue($reply->requestLead);
    }

    private function buildResponder(): ChatResponder
    {
        $qualificationService = new ChatQualificationService();

        return new ChatResponder(
            new PublicContentCatalog(
                $this->createMock(SitePageRepository::class),
                $this->createMock(PracticeRepository::class),
                $this->createMock(ServicesRepository::class),
                $this->createMock(ProjetRepository::class),
                $this->createMock(TeamRepository::class),
                $this->createMock(UrlGeneratorInterface::class),
            ),
            $qualificationService,
            new HeuristicAiProvider($qualificationService),
            [],
            new NullLogger(),
        );
    }
}
