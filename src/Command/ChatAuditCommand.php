<?php

namespace App\Command;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\ChatResponder;
use App\Service\Chat\PublicContentCatalog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:chat:audit')]
class ChatAuditCommand extends Command
{
    public function __construct(
        private readonly ChatResponder $chatResponder,
        private readonly PublicContentCatalog $publicContentCatalog,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $questions = [
            'projet SI client et facturation avez vous des références eaux et assainissement ?',
            'vous faites amoa progiciel ?',
            'eaux et assainissement ?',
            'comment accompagnez-vous un projet ERP ?',
            'quelles solutions et progiciels maîtrisez-vous ?',
            'quel expert oling pour mon projet erp ?',
            'faites-vous des audits rgpd ?',
            'amoa iso27001',
            'notre ERP est obsolète, vous intervenez sur le cadrage ?',
            'avez-vous des références dans l industrie ?',
            'vous connaissez sage x3 ?',
            'vous faites gmao ?',
            'on cherche une aide pour reprise de données et interfaces',
            'vous intervenez sur la facturation électronique ?',
            'je veux parler à un consultant',
            'votre numéro de téléphone ?',
            'qui chez oling pour un projet crm ?',
            'avez-vous déjà travaillé avec veolia ?',
            'besoin d un dpo externe, vous faites ?',
            'projet multi sites avec SI finance et reporting, quelle approche ?',
        ];

        $rows = [];
        $openAiCount = 0;
        $heuristicCount = 0;
        $totalMs = 0;

        foreach ($questions as $question) {
            $conversation = new ChatConversation();
            $message = (new ChatMessage())
                ->setRole('visitor')
                ->setMessageType('answer')
                ->setContent($question)
                ->setSequenceNumber(1)
                ->setCreatedAt(new \DateTimeImmutable());
            $conversation->addMessage($message);

            $documents = $this->publicContentCatalog->findRelevantDocuments($question, null, 8);

            $startedAt = microtime(true);
            $reply = $this->chatResponder->reply($conversation, $question);
            $durationMs = (int) round((microtime(true) - $startedAt) * 1000);

            if ($reply->provider === 'openai') {
                ++$openAiCount;
            } elseif ($reply->provider === 'heuristic') {
                ++$heuristicCount;
            }

            $totalMs += $durationMs;

            $rows[] = [
                'question' => $question,
                'provider' => $reply->provider,
                'duration_ms' => $durationMs,
                'message_type' => $reply->messageType,
                'retrieval_count' => count($documents),
                'retrieval' => array_map(
                    static fn (array $document): string => sprintf('%s | %s', $document['type'], $document['title']),
                    array_slice($documents, 0, 4)
                ),
                'sources' => $reply->sources,
                'preview' => mb_substr($reply->content, 0, 260),
            ];
        }

        $io->writeln(json_encode([
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'count' => count($questions),
            'openai_success_rate' => round(($openAiCount / count($questions)) * 100, 1),
            'heuristic_fallback_rate' => round(($heuristicCount / count($questions)) * 100, 1),
            'average_provider_latency_ms' => round($totalMs / count($questions), 1),
            'rows' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }
}
