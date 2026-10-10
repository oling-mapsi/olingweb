<?php

namespace App\Service\Chat;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class ScopingNotePdfService
{
    private const RETENTION = '+30 days';

    public function __construct(
        private readonly Environment $twig,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $projectDir,
    ) {
    }

    /**
     * @return array{token:string,downloadUrl:string,expiresAt:string}
     */
    public function create(ChatConversation $conversation): array
    {
        $message = $this->latestEligibleMessage($conversation);
        if (!$message instanceof ChatMessage) {
            throw new \InvalidArgumentException('Aucune note de cadrage générée n’est disponible pour cette conversation.');
        }

        $noteToken = bin2hex(random_bytes(24));
        $expiresAt = (new \DateTimeImmutable())->modify(self::RETENTION);
        $directory = $this->noteDirectory($conversation);
        if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
            throw new \RuntimeException('Impossible de préparer le stockage privé de la note.');
        }

        $payload = $this->structuredPayload($conversation, $message);
        file_put_contents($directory.'/'.$noteToken.'.pdf', $this->renderPdf($conversation, $payload));
        file_put_contents($directory.'/'.$noteToken.'.json', json_encode([
            'conversationToken' => $conversation->getPublicToken(),
            'createdAt' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'expiresAt' => $expiresAt->format(DATE_ATOM),
            'sourceMessageId' => $message->getId(),
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));

        return [
            'token' => $noteToken,
            'downloadUrl' => $this->urlGenerator->generate('api_chat_conversation_scoping_note_download', [
                'token' => $conversation->getPublicToken(),
                'noteToken' => $noteToken,
            ]),
            'expiresAt' => $expiresAt->format(DATE_ATOM),
        ];
    }

    public function pdfPath(ChatConversation $conversation, string $noteToken): string
    {
        $this->assertToken($noteToken);
        $directory = $this->noteDirectory($conversation);
        $metadataPath = $directory.'/'.$noteToken.'.json';
        $pdfPath = $directory.'/'.$noteToken.'.pdf';
        if (!is_file($metadataPath) || !is_file($pdfPath)) {
            throw new \InvalidArgumentException('Note introuvable ou expirée.');
        }

        $metadata = json_decode((string) file_get_contents($metadataPath), true, 512, JSON_THROW_ON_ERROR);
        if (($metadata['conversationToken'] ?? null) !== $conversation->getPublicToken()) {
            throw new \InvalidArgumentException('Accès non autorisé à cette note.');
        }

        $expiresAt = new \DateTimeImmutable((string) $metadata['expiresAt']);
        if ($expiresAt < new \DateTimeImmutable()) {
            @unlink($metadataPath);
            @unlink($pdfPath);
            throw new \InvalidArgumentException('Note expirée.');
        }

        return $pdfPath;
    }

    public function filename(ChatConversation $conversation): string
    {
        $need = preg_replace('/[^a-z0-9]+/i', '-', strtolower((string) ($conversation->getQualification()['primary_need'] ?? 'projet'))) ?: 'projet';

        return sprintf('note-cadrage-oling-%s.pdf', trim($need, '-'));
    }

    public function purgeExpired(): int
    {
        $base = $this->baseDirectory();
        if (!is_dir($base)) {
            return 0;
        }

        $count = 0;
        foreach (glob($base.'/*/*.json') ?: [] as $metadataPath) {
            $metadata = json_decode((string) file_get_contents($metadataPath), true) ?: [];
            $expiresAt = isset($metadata['expiresAt']) ? new \DateTimeImmutable((string) $metadata['expiresAt']) : null;
            if (!$expiresAt || $expiresAt > new \DateTimeImmutable()) {
                continue;
            }
            $pdfPath = substr($metadataPath, 0, -5).'.pdf';
            @unlink($metadataPath);
            @unlink($pdfPath);
            ++$count;
        }

        return $count;
    }

    private function renderPdf(ChatConversation $conversation, array $payload): string
    {
        $html = $this->twig->render('chat/scoping_note_pdf.html.twig', [
            'conversation' => $conversation,
            'note' => $payload,
            'generatedAt' => new \DateTimeImmutable(),
            'logoDataUri' => $this->logoDataUri(),
        ]);

        $options = new Options();
        $options->set('defaultFont', 'DejaVu Sans');
        $options->set('isRemoteEnabled', false);
        $options->set('chroot', $this->projectDir);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return (string) $dompdf->output();
    }

    /**
     * @return array<string, mixed>
     */
    private function structuredPayload(ChatConversation $conversation, ChatMessage $message): array
    {
        $qualification = $conversation->getQualification();
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $message->getContent()) ?: [])));

        return [
            'title' => 'OLING — Première note de cadrage',
            'subtitle' => 'Analyse préliminaire de votre projet',
            'need' => $qualification['primary_need'] ?? 'Projet à cadrer',
            'sections' => [
                'Synthèse exécutive' => $this->sliceText($lines, 0),
                'Contexte et situation actuelle' => $this->sliceText($lines, 1),
                'Objectifs identifiés' => $this->sliceText($lines, 2),
                'Périmètre du projet' => $this->sliceText($lines, 3),
                'Principaux constats et points de vigilance' => $this->sliceText($lines, 4),
                'Scénarios envisageables' => $this->sliceText($lines, 5),
                'Premières recommandations' => $this->sliceText($lines, 6),
                'Démarche d’accompagnement envisageable' => $this->sliceText($lines, 7),
                'Informations à compléter' => $this->sliceText($lines, 8),
                'Prochaine étape' => $this->sliceText($lines, 9),
            ],
        ];
    }

    private function sliceText(array $lines, int $offset): string
    {
        if ($lines === []) {
            return 'Information à compléter lors d’un échange de cadrage.';
        }

        $chunkSize = max(1, (int) ceil(count($lines) / 10));
        $slice = array_slice($lines, $offset * $chunkSize, $chunkSize);

        return trim(implode("\n", $slice)) ?: 'Information à compléter lors d’un échange de cadrage.';
    }

    private function latestEligibleMessage(ChatConversation $conversation): ?ChatMessage
    {
        $messages = array_reverse($conversation->getMessages()->toArray());
        foreach ($messages as $message) {
            if ($message instanceof ChatMessage && $message->getRole() === 'assistant' && in_array($message->getMessageType(), ['scoping_note', 'diagnostic'], true)) {
                return $message;
            }
        }

        return null;
    }

    private function noteDirectory(ChatConversation $conversation): string
    {
        return $this->baseDirectory().'/'.hash('sha256', (string) $conversation->getPublicToken());
    }

    private function baseDirectory(): string
    {
        return $this->projectDir.'/var/private/chat_scoping_notes';
    }

    private function assertToken(string $token): void
    {
        if (preg_match('/^[a-f0-9]{48}$/', $token) !== 1) {
            throw new \InvalidArgumentException('Jeton de note invalide.');
        }
    }

    private function logoDataUri(): ?string
    {
        $path = $this->projectDir.'/public/img/logo/logoling.png';
        if (!is_file($path)) {
            return null;
        }

        $content = file_get_contents($path);

        return $content === false ? null : 'data:image/png;base64,'.base64_encode($content);
    }
}
