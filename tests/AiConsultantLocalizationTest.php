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

    public function testChatConversationStoresRequestedLocaleAndPromptVersion(): void
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
        self::assertSame('en', $conversation->getLocale());
        self::assertSame('v1', $conversation->getPromptVersion());
    }

    public function testLocalizedJsonContractsMatchFrenchSource(): void
    {
        $this->assertSameJsonShape('ai_consultant', ['en', 'es']);
    }

    private function contentProvider(): AiConsultantContentProvider
    {
        return new AiConsultantContentProvider(dirname(__DIR__));
    }

    /**
     * @param string[] $locales
     */
    private function assertSameJsonShape(string $name, array $locales): void
    {
        $base = dirname(__DIR__).'/data/i18n/'.$name;
        $source = $this->jsonPaths(json_decode((string) file_get_contents($base.'.fr.json'), true, 512, JSON_THROW_ON_ERROR));
        foreach ($locales as $locale) {
            self::assertSame(
                $source,
                $this->jsonPaths(json_decode((string) file_get_contents($base.'.'.$locale.'.json'), true, 512, JSON_THROW_ON_ERROR)),
                $name.'.'.$locale.' keys must match FR'
            );
        }
    }

    /**
     * @return string[]
     */
    private function jsonPaths(mixed $value, string $prefix = ''): array
    {
        if (!is_array($value)) {
            return [];
        }

        $paths = [];
        foreach ($value as $key => $child) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            $paths[] = $path;
            array_push($paths, ...$this->jsonPaths($child, $path));
        }

        return $paths;
    }
}
