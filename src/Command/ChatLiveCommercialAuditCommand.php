<?php

namespace App\Command;

use App\Entity\ChatConversation;
use App\Entity\ChatMessage;
use App\Service\Chat\ChatResponder;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:chat:live-commercial-audit')]
class ChatLiveCommercialAuditCommand extends Command
{
    public function __construct(private readonly ChatResponder $chatResponder)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $rows = [];
        foreach ($this->scenarios() as $scenario) {
            $conversation = new ChatConversation();
            $turns = $scenario['turns'];
            $lastReply = null;

            foreach ($turns as $index => $message) {
                $conversation->addMessage($this->message('visitor', $message, ($index * 2) + 1));
                $startedAt = microtime(true);
                $lastReply = $this->chatResponder->reply($conversation, $message);
                $latencyMs = (int) round((microtime(true) - $startedAt) * 1000);
                $conversation->addMessage($this->message('assistant', $lastReply->content, ($index * 2) + 2, $lastReply->messageType));
            }

            if ($lastReply === null) {
                continue;
            }

            $actions = array_column($lastReply->actions, 'type');
            $rows[] = [
                'id' => $scenario['id'],
                'kind' => $scenario['kind'],
                'question' => end($turns),
                'provider' => $lastReply->provider,
                'model' => $lastReply->model,
                'message_type' => $lastReply->messageType,
                'request_lead' => $lastReply->requestLead,
                'actions' => $actions,
                'contact_button' => in_array('open_lead_form', $actions, true),
                'latency_ms' => $latencyMs,
                'sources' => $lastReply->sources,
                'qualification' => $lastReply->qualification,
                'reply' => $lastReply->content,
                'pass' => $this->passes($scenario['kind'], $lastReply->messageType, $actions),
            ];
        }

        $commercialRows = array_values(array_filter($rows, static fn (array $row): bool => $row['kind'] === 'commercial'));
        $commercialWithContact = count(array_filter($commercialRows, static fn (array $row): bool => $row['contact_button']));

        $output->writeln(json_encode([
            'timestamp' => (new \DateTimeImmutable())->format(DATE_ATOM),
            'rows' => $rows,
            'summary' => [
                'total' => count($rows),
                'commercial_total' => count($commercialRows),
                'commercial_contact_rate' => count($commercialRows) > 0 ? round(($commercialWithContact / count($commercialRows)) * 100, 1) : 0,
                'failures' => array_values(array_filter($rows, static fn (array $row): bool => !$row['pass'])),
            ],
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return Command::SUCCESS;
    }

    private function message(string $role, string $content, int $sequence, string $type = 'answer'): ChatMessage
    {
        return (new ChatMessage())
            ->setRole($role)
            ->setContent($content)
            ->setMessageType($type)
            ->setSequenceNumber($sequence)
            ->setCreatedAt(new \DateTimeImmutable());
    }

    /**
     * @return array<int, array{id:string, kind:string, turns:list<string>}>
     */
    private function scenarios(): array
    {
        return [
            ['id' => 'dora_asset_manager', 'kind' => 'commercial', 'turns' => ['Bonjour, je suis une société de gestion des actifs financiers. Nous devons être conformes à DORA. Avez-vous cette expérience ?']],
            ['id' => 'erp_amoa', 'kind' => 'commercial', 'turns' => ['Nous souhaitons remplacer notre ERP et recherchons une AMOA indépendante.']],
            ['id' => 'dpo_externalise', 'kind' => 'commercial', 'turns' => ['Nous recherchons un DPO externalisé pour notre organisation.']],
            ['id' => 'rfe_cabinet', 'kind' => 'commercial', 'turns' => ['Nous devons mettre en œuvre la réforme de la facturation électronique et cherchons un cabinet pour nous accompagner.']],
            ['id' => 'gmao', 'kind' => 'commercial', 'turns' => ['Nous envisageons de remplacer notre GMAO pour des services publics.']],
            ['id' => 'crm', 'kind' => 'commercial', 'turns' => ['Nous devons lancer une consultation CRM.']],
            ['id' => 'si_finance', 'kind' => 'commercial', 'turns' => ['Nous voulons cadrer notre SI Finance et nos flux de reporting.']],
            ['id' => 'sirh', 'kind' => 'commercial', 'turns' => ['Nous avons un projet SIRH avec données sensibles RH.']],
            ['id' => 'dsi_transition', 'kind' => 'commercial', 'turns' => ['Nous cherchons un DSI de transition.']],
            ['id' => 'iso27001', 'kind' => 'commercial', 'turns' => ['Pouvez-vous nous accompagner sur ISO 27001 ?']],
            ['id' => 'nis2', 'kind' => 'commercial', 'turns' => ['Nous avons besoin d’un accompagnement sur NIS2.']],
            ['id' => 'qse', 'kind' => 'commercial', 'turns' => ['Nous devons passer ISO 9001 et structurer notre démarche QSE.']],
            ['id' => 'mapsi_demo', 'kind' => 'commercial', 'turns' => ['Nous aimerions faire une démonstration MAPSI.']],
            ['id' => 'sap_integrator', 'kind' => 'commercial', 'turns' => ['Notre intégrateur SAP est en difficulté, pouvez-vous intervenir ?']],
            ['id' => 'sage_x3', 'kind' => 'commercial', 'turns' => ['Nous avons un projet de migration Sage X3.']],
            ['id' => 'simple_dora_info', 'kind' => 'no_sell', 'turns' => ['DORA, c’est quoi ?']],
            ['id' => 'student_amoa', 'kind' => 'no_sell', 'turns' => ['Je suis étudiant, pouvez-vous me définir l’AMOA ?']],
            ['id' => 'refusal', 'kind' => 'no_sell', 'turns' => ['Nous recherchons une AMOA ERP, mais pas de contact maintenant.']],
            ['id' => 'off_topic', 'kind' => 'no_sell', 'turns' => ['Pouvez-vous me donner une recette de gâteau ?']],
            ['id' => 'diagnostic_preference', 'kind' => 'no_sell', 'turns' => ['Nous avons un sujet ERP mais je préfère poursuivre le diagnostic avant tout contact.']],
            ['id' => 'rfe_sequence', 'kind' => 'commercial', 'turns' => ['accompagnement amoa SI finance ?', 'un cadrage en amont', 'la rfe', 'salesforce', 'dolibarr']],
        ];
    }

    /**
     * @param string[] $actions
     */
    private function passes(string $kind, string $messageType, array $actions): bool
    {
        if ($kind === 'commercial') {
            return in_array('open_lead_form', $actions, true);
        }

        return $messageType !== 'lead_request' && !in_array('open_lead_form', $actions, true);
    }
}
