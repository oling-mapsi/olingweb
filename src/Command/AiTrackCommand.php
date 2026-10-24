<?php
namespace App\Command;

use App\Service\Ai\{AiNativeRegistry,AiTrackingContract};
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\{InputInterface,InputOption,StreamableInputInterface};
use Symfony\Component\Console\Output\OutputInterface;

/** One operator-authorized invocation, not an authentication or execution service. */
#[AsCommand(name: 'app:ai:track', description: 'Synchroniser un événement de suivi IA via JSON stdin, sans lancer de tâche.')]
final class AiTrackCommand extends Command
{
    private const OPERATIONS = [
        'ai_missions_list', 'ai_mission_get', 'ai_mission_create', 'ai_mission_prompt',
        'ai_task_associate', 'ai_task_event', 'ai_task_result', 'ai_mission_review',
    ];
    private const MAX_BYTES = 65536;

    public function __construct(private Connection $db, private AiNativeRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('owner-username', null, InputOption::VALUE_REQUIRED, 'Identifiant exact du propriétaire autorisé, fourni hors JSON.')
            ->addOption('actor-task-id', null, InputOption::VALUE_REQUIRED, 'UUID du fil pilote fourni par l’opérateur ; non vérifié par cette commande.')
            ->addOption('native-task-id', null, InputOption::VALUE_REQUIRED, 'UUID de réalisation/revue confirmé hors JSON pour une association/revue.')
            ->addOption('apply', null, InputOption::VALUE_NONE, 'Autoriser la mutation demandée pour cette invocation uniquement.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $username = $input->getOption('owner-username');
            $pilot = $input->getOption('actor-task-id');
            $subject = $input->getOption('native-task-id');
            if ($input->isInteractive() || !is_string($username) || $username === '' || strlen($username) > 180
                || preg_match('/[\x00-\x1F\x7F]/', $username) || !self::taskId($pilot)
                || ($subject !== null && !self::taskId($subject))) {
                return $this->failure($output, 'invalid_invocation', self::INVALID);
            }
            $stream = $input instanceof StreamableInputInterface ? $input->getStream() : null;
            $stream ??= STDIN;
            if (stream_isatty($stream)) {
                return $this->failure($output, 'json_stdin_required', self::INVALID);
            }
            $raw = stream_get_contents($stream, self::MAX_BYTES + 1);
            if ($raw === false || strlen($raw) > self::MAX_BYTES) {
                return $this->failure($output, 'payload_too_large', self::INVALID);
            }
            $payload = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
            if (!is_array($payload) || count($payload) !== 2 || array_diff(array_keys($payload), ['operation', 'arguments'])
                || !is_string($payload['operation'] ?? null) || !is_array($payload['arguments'] ?? null)
                || !in_array($payload['operation'], self::OPERATIONS, true)) {
                return $this->failure($output, 'invalid_request', self::INVALID);
            }
            $operation = $payload['operation'];
            $arguments = $payload['arguments'];
            AiTrackingContract::validate($operation, $arguments);
            $referenceField = match ($operation) {
                'ai_task_associate' => 'task_id',
                'ai_mission_review' => 'review_task_id',
                default => null,
            };
            if (($referenceField !== null && ($subject === null || $arguments[$referenceField] !== $subject))
                || ($referenceField === null && $subject !== null)) {
                return $this->failure($output, 'task_reference_mismatch', self::INVALID);
            }
            if (!in_array($operation, ['ai_missions_list', 'ai_mission_get'], true) && !$input->getOption('apply')) {
                return $this->failure($output, 'mutation_requires_apply', self::INVALID);
            }
            // Never hydrate User: do not select a password/hash or any other account field.
            $owners = $this->db->fetchAllAssociative('SELECT id, username, roles FROM app_user WHERE username = ? LIMIT 2', [$username]);
            if (count($owners) !== 1 || !hash_equals($username, $owners[0]['username'])) {
                return $this->failure($output, 'owner_not_allowed', 3);
            }
            $roles = json_decode($owners[0]['roles'], true, 8, JSON_THROW_ON_ERROR);
            if (!is_array($roles) || !array_is_list($roles) || count(array_filter($roles, 'is_string')) !== count($roles)
                || !array_intersect(['ROLE_ADMIN', 'ROLE_SUPER_ADMIN'], $roles)) {
                return $this->failure($output, 'owner_not_allowed', 3);
            }
            $result = $this->registry->call($operation, $arguments, (int) $owners[0]['id'], 'native-'.$pilot);
            $this->write($output, [
                'ok' => true,
                'identity_source' => 'operator_supplied_cli_options',
                'native_task_verified' => false,
                'task_reference_source' => 'operator_declared_not_platform_verified',
                'execution_triggered' => false,
                'result' => $result,
            ]);
            return self::SUCCESS;
        } catch (\OutOfBoundsException) {
            return $this->failure($output, 'not_found', 3);
        } catch (\DomainException|\Doctrine\DBAL\Exception\RetryableException) {
            return $this->failure($output, 'conflict_retry_same_event_or_reconcile', 4);
        } catch (\InvalidArgumentException|\JsonException|\TypeError) {
            return $this->failure($output, 'invalid_request', self::INVALID);
        } catch (\Throwable) {
            // No exception messages, SQL, payload, credentials or stack traces in command output.
            return $this->failure($output, 'tracking_unavailable', self::FAILURE);
        }
    }

    private static function taskId(mixed $value): bool
    {
        return is_string($value) && (bool) preg_match('/^[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}$/D', $value);
    }

    private function failure(OutputInterface $output, string $error, int $code): int
    {
        $this->write($output, ['ok' => false, 'error' => $error]);
        return $code;
    }

    private function write(OutputInterface $output, array $value): void
    {
        $output->writeln(json_encode($value, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), OutputInterface::OUTPUT_RAW);
    }
}
