<?php

namespace App\Tests;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\ScopingNotePdfService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class ScopingNotePdfServiceTest extends TestCase
{
    public function testServerPdfIsGeneratedAndBoundToConversation(): void
    {
        $service = $this->service();
        $conversation = $this->conversation('token-a');

        $note = $service->create($conversation);
        $path = $service->pdfPath($conversation, $note['token']);

        self::assertFileExists($path);
        self::assertStringStartsWith('%PDF', (string) file_get_contents($path));
        self::assertStringEndsWith('download', $note['downloadUrl']);
    }

    public function testCrossConversationAccessIsRejected(): void
    {
        $service = $this->service();
        $note = $service->create($this->conversation('token-a'));

        $this->expectException(\InvalidArgumentException::class);
        $service->pdfPath($this->conversation('token-b'), $note['token']);
    }

    public function testNoFakePdfWithoutGeneratedNote(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->service()->create((new ChatConversation())->setPublicToken('empty'));
    }

    private function conversation(string $token): ChatConversation
    {
        $conversation = (new ChatConversation())
            ->setPublicToken($token)
            ->setQualification(['primary_need' => 'crm']);
        $conversation->addMessage((new ChatMessage())
            ->setRole('assistant')
            ->setMessageType('scoping_note')
            ->setContent("Synthèse exécutive CRM Maison&Objet.\nArchitecture cible, interfaces, gouvernance des données.\nRecommandations AMOA indépendante.")
            ->setSequenceNumber(1)
            ->setCreatedAt(new \DateTimeImmutable()));

        return $conversation;
    }

    private function service(): ScopingNotePdfService
    {
        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator
            ->method('generate')
            ->willReturn('/api/chat/conversations/token/scoping-notes/note/download');

        return new ScopingNotePdfService(
            new Environment(new FilesystemLoader(dirname(__DIR__).'/templates')),
            $urlGenerator,
            dirname(__DIR__)
        );
    }
}
