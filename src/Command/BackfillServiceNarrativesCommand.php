<?php

namespace App\Command;

use App\Entity\SitePageTranslation;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:i18n:backfill-service-narratives',
    description: 'Backfill les narratives publiques services FR dans service_translation.',
)]
class BackfillServiceNarrativesCommand extends Command
{
    private const SOURCE_PATH = '/data/i18n/service_narratives.fr.json';

    public function __construct(
        private readonly Connection $connection,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide sans ecrire.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace un payload different.')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limite le backfill a une ou plusieurs cles practice/service.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $onlySlugs = array_filter(array_map('strval', (array) $input->getOption('slug')));
        $narratives = $this->loadNarratives();
        $counts = ['write' => 0, 'unchanged' => 0, 'conflict' => 0, 'missing' => 0];
        $report = [];

        foreach ($narratives as $key => $payload) {
            if ($onlySlugs !== [] && !in_array((string) $key, $onlySlugs, true)) {
                continue;
            }
            [$practiceSlug, $serviceSlug] = explode('/', $key, 2);
            $row = $this->connection->fetchAssociative(
                'SELECT st.id, st.public_narrative FROM service_translation st INNER JOIN services s ON s.id = st.service_id INNER JOIN practice p ON p.id = s.practice_id WHERE st.locale = :locale AND p.slug = :practice AND s.slug = :service',
                ['locale' => SitePageTranslation::LOCALE_FR, 'practice' => $practiceSlug, 'service' => $serviceSlug]
            );

            if (!is_array($row)) {
                ++$counts['missing'];
                $report[] = [$key, 'service_translation', 'NO', 'MISSING'];
                continue;
            }

            $existing = is_string($row['public_narrative'] ?? null) && trim($row['public_narrative']) !== ''
                ? json_decode($row['public_narrative'], true, 512, JSON_THROW_ON_ERROR)
                : null;

            if ($this->canonicalJson($existing) === $this->canonicalJson($payload)) {
                ++$counts['unchanged'];
                $report[] = [$key, 'service_translation', 'YES', 'UNCHANGED'];
                continue;
            }

            if ($existing !== null && !$overwrite) {
                ++$counts['conflict'];
                $report[] = [$key, 'service_translation', 'YES', 'CONFLICT_SKIP'];
                continue;
            }

            ++$counts['write'];
            $report[] = [$key, 'service_translation', 'YES', $dryRun ? 'WOULD_WRITE' : ($overwrite ? 'OVERWRITE' : 'WRITE')];
            if (!$dryRun) {
                $this->connection->update('service_translation', [
                    'public_narrative' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ], ['id' => $row['id']]);
            }
        }

        $io->table(['Source', 'Target type', 'FR translation found', 'Action'], $report);
        $io->success(sprintf('rows=%d write=%d unchanged=%d conflict=%d missing=%d dry_run=%s overwrite=%s', count($narratives), $counts['write'], $counts['unchanged'], $counts['conflict'], $counts['missing'], $dryRun ? 'yes' : 'no', $overwrite ? 'yes' : 'no'));

        return $counts['missing'] > 0 || $counts['conflict'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadNarratives(): array
    {
        $decoded = json_decode((string) file_get_contents($this->kernel->getProjectDir() . self::SOURCE_PATH), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['locale'] ?? null) !== SitePageTranslation::LOCALE_FR || !is_array($decoded['narratives'] ?? null)) {
            throw new \RuntimeException('Invalid service narratives source file.');
        }

        return $decoded['narratives'];
    }

    private function canonicalJson(mixed $value): string
    {
        return json_encode($this->sortRecursively($value), JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    private function sortRecursively(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }
        foreach ($value as $key => $item) {
            $value[$key] = $this->sortRecursively($item);
        }
        if (!array_is_list($value)) {
            ksort($value);
        }
        return $value;
    }
}
