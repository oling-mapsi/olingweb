<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Service\Chat\AiConsultantContentProvider;
use App\Service\Chat\ChatConversationManager;
use App\Service\Chat\ChatLeadMailer;
use App\Service\Chat\ChatQualificationService;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\ChatSummaryService;
use App\Service\Chat\PublicContentCatalog;
use App\Repository\ChatConversationRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class AiConsultantLocalizationTest extends TestCase
{
    public function testPromptProviderLoadsFrenchVersionedPrompts(): void
    {
        $provider = $this->contentProvider();
        $content = $provider->content();

        self::assertSame('fr', $content['meta']['locale']);
        self::assertSame('v1', $content['meta']['version']);
        self::assertSame('v1', $content['prompts']['chat.system']['version']);
        self::assertStringContainsString('Tu es l’assistant expert d’OLING.', $provider->prompt('chat.system'));
        $rendered = $provider->renderPrompt('chat.user', [
            'sourceUrl' => 'https://oling.fr/',
            'sourcePath' => '/',
            'qualification' => '{}',
            'history' => '- none',
            'visitorMessage' => 'Bonjour',
            'snippets' => '- none',
        ]);
        self::assertStringContainsString('Latest visitor message:', $rendered);
        self::assertStringContainsString('Bonjour', $rendered);
    }

    public function testChatConversationStoresFrenchLocaleAndPromptVersion(): void
    {
        $conversation = null;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist')->willReturnCallback(static function (object $entity) use (&$conversation): void {
            if ($entity instanceof ChatConversation) {
                $conversation = $entity;
            }
        });

        $manager = new ChatConversationManager(
            $entityManager,
            $this->createMock(ChatConversationRepository::class),
            $this->createMock(ChatResponder::class),
            new ChatQualificationService(),
            new ChatSummaryService($this->contentProvider()),
            $this->createMock(ChatLeadMailer::class),
            $this->createMock(PublicContentCatalog::class),
            $this->contentProvider()
        );

        $manager->createConversation('/test', 'https://oling.fr/test', null, 'en', '127.0.0.1', 'Test');

        self::assertInstanceOf(ChatConversation::class, $conversation);
        self::assertSame('fr', $conversation->getLocale());
        self::assertSame('v1', $conversation->getPromptVersion());
    }

    private function contentProvider(): AiConsultantContentProvider
    {
        return new AiConsultantContentProvider(dirname(__DIR__));
    }
}
