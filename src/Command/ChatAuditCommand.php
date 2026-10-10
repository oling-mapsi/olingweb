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
            'On veut changer Sage.',
            'Notre ERP devient ingérable.',
            'Je cherche quelqu’un pour faire le cahier des charges ERP.',
            'DAF: nous devons changer notre ERP finance.',
            'Je dois choisir entre SAP et Dynamics.',
            'Vous connaissez Sage X3 ?',
            'Notre intégrateur ERP est en retard.',
            'On cherche une aide pour reprise de données et interfaces.',
            'AMOA GMAO eau et assainissement: vous intervenez ?',
            'CRM sur mesure avec contraintes cyber, quelle approche ?',
            'Nous avons un projet SIRH.',
            'Vous intervenez sur la facturation électronique ?',
            'Projet multi-sites avec SI finance et reporting, quelle approche ?',
            'Nous voulons mettre en place Power BI.',
            'Nous devons développer une application métier spécifique.',
            'Nous voulons faire un schéma directeur SI.',
            'Je voudrais externaliser une partie de ma DSI.',
            'Nous cherchons un DSI de transition.',
            'Nous avons besoin de PMO sur un programme SI.',
            'Transformation digitale PME: par où commencer ?',
            'Est-ce que NIS2 me concerne ?',
            'DORA: que devons-nous préparer ?',
            'On doit passer ISO 27001.',
            'Nous voulons un audit cyber.',
            'PCA/PRA: comment structurer le projet ?',
            'ISO 22301: pouvez-vous accompagner ?',
            'Nous avons un contrôle CNIL.',
            'Mon DPO part dans trois mois.',
            'Faites-vous des audits RGPD ?',
            'DPIA sur un outil RH: comment faire ?',
            'Nous devons remettre à jour notre registre des traitements.',
            'Besoin d un DPO externe pour une collectivité.',
            'Nous devons passer ISO 9001.',
            'ISO 14001 et QSE: vous faites ?',
            'Qualiopi: peut-on structurer le pilotage ?',
            'On cherche un logiciel pour gérer nos plans d’action qualité.',
            'Contrôle interne: comment organiser les preuves ?',
            'Je cherche un logiciel GRC.',
            'Logiciel RGPD: MAPSI peut convenir ?',
            'Logiciel ISO 27001: que proposez-vous ?',
            'ITSM et cartographie SI dans MAPSI ?',
            'AI Act: comment cartographier nos usages IA ?',
            'Agents IA métier: comment cadrer le risque ?',
            'DG: nous devons prioriser nos projets SI.',
            'DSI: nous avons trop de dépendances applicatives.',
            'DAF: combien coûte une AMOA ERP ?',
            'DRH: données sensibles dans un projet SIRH.',
            'Nous préparons un marché public.',
            'Vous êtes une petite structure.',
            'Pourquoi vous plutôt qu’un grand cabinet ?',
            'Nous avons déjà un intégrateur.',
            'Nous voulons simplement un consultant freelance.',
            'Votre prix semble élevé.',
            'Nous ne voulons pas changer tous nos outils.',
            'Est-ce que vous intervenez en Guadeloupe ?',
            'Avez-vous déjà travaillé avec Veolia ?',
            'Projet bloqué urgent, pouvez-vous nous aider vite ?',
            'Je veux parler à un consultant.',
            'Votre numéro de téléphone ?',
            'Pouvez-vous relire notre expression de besoin ?',
        ];

        $rows = [];
        $openAiCount = 0;
        $unavailableCount = 0;
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
            } elseif ($reply->provider === 'llm_unavailable') {
                ++$unavailableCount;
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
            'llm_unavailable_rate' => round(($unavailableCount / count($questions)) * 100, 1),
            'average_provider_latency_ms' => round($totalMs / count($questions), 1),
            'rows' => $rows,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }
}
