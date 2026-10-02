<?php

namespace App\Command;

use App\Entity\SitePageTranslation;
use App\Entity\Team;
use App\Repository\TeamRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:i18n:backfill-team-profiles',
    description: 'Backfill les profils publics equipe FR dans team_translation.',
)]
class BackfillTeamProfilesCommand extends Command
{
    private const SOURCE_PATH = '/data/i18n/team_profiles.fr.json';

    public function __construct(
        private readonly TeamRepository $teamRepository,
        private readonly Connection $connection,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide sans ecrire.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace un profil different.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $profiles = $this->loadProfiles();
        $teams = $this->teamRepository->findAll();
        $teamsByName = [];
        foreach ($teams as $team) {
            $teamsByName[$this->normalize((string) $team->getNoncomplet())] = $team;
        }

        $counts = ['write' => 0, 'unchanged' => 0, 'conflict' => 0, 'missing' => 0];
        $report = [];
        foreach ($profiles as $profile) {
            $team = $teamsByName[$this->normalize((string) $profile['name'])] ?? null;
            if (!$team instanceof Team || $team->getId() === null) {
                ++$counts['missing'];
                $report[] = [$profile['displayName'] ?? $profile['name'], 'NO', 'NO', 'MISSING_TEAM'];
                continue;
            }

            $row = $this->connection->fetchAssociative('SELECT id, public_profile FROM team_translation WHERE team_id = :id AND locale = :locale', [
                'id' => $team->getId(),
                'locale' => SitePageTranslation::LOCALE_FR,
            ]);
            if (!is_array($row)) {
                ++$counts['missing'];
                $report[] = [$profile['displayName'] ?? $profile['name'], 'YES', 'NO', 'MISSING_TRANSLATION'];
                continue;
            }

            $existing = is_string($row['public_profile'] ?? null) ? json_decode($row['public_profile'], true, 512, JSON_THROW_ON_ERROR) : null;
            if ($this->canonicalJson($existing) === $this->canonicalJson($profile)) {
                ++$counts['unchanged'];
                $report[] = [$profile['displayName'], 'YES', 'YES', 'UNCHANGED'];
                continue;
            }

            if ($existing !== null && !$overwrite) {
                ++$counts['conflict'];
                $report[] = [$profile['displayName'], 'YES', 'YES', 'CONFLICT_SKIP'];
                continue;
            }

            ++$counts['write'];
            $report[] = [$profile['displayName'], 'YES', 'YES', $dryRun ? 'WOULD_WRITE' : 'WRITE'];
            if (!$dryRun) {
                $this->connection->update('team_translation', [
                    'public_profile' => json_encode($profile, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'shortcv' => $profile['shortcv'] ?? null,
                    'titre' => $profile['titre'] ?? null,
                    'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
                ], ['id' => $row['id']]);
            }
        }

        $io->table(['Profile', 'Team', 'FR Translation', 'Action'], $report);
        $io->success(sprintf('rows=%d write=%d unchanged=%d conflict=%d missing=%d dry_run=%s overwrite=%s', count($profiles), $counts['write'], $counts['unchanged'], $counts['conflict'], $counts['missing'], $dryRun ? 'yes' : 'no', $overwrite ? 'yes' : 'no'));

        return $counts['missing'] > 0 || $counts['conflict'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadProfiles(): array
    {
        $decoded = json_decode((string) file_get_contents($this->kernel->getProjectDir() . self::SOURCE_PATH), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['locale'] ?? null) !== SitePageTranslation::LOCALE_FR || !is_array($decoded['profiles'] ?? null)) {
            throw new \RuntimeException('Invalid team profiles source file.');
        }

        return $decoded['profiles'];
    }

    private function normalize(string $value): string
    {
        $value = strtr($value, [
            'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a', 'å' => 'a',
            'ç' => 'c',
            'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
            'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i',
            'ñ' => 'n',
            'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'û' => 'u', 'ü' => 'u',
            'ý' => 'y', 'ÿ' => 'y',
            'À' => 'a', 'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Ã' => 'a', 'Å' => 'a',
            'Ç' => 'c',
            'È' => 'e', 'É' => 'e', 'Ê' => 'e', 'Ë' => 'e',
            'Ì' => 'i', 'Í' => 'i', 'Î' => 'i', 'Ï' => 'i',
            'Ñ' => 'n',
            'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Ö' => 'o', 'Õ' => 'o',
            'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u',
            'Ý' => 'y',
        ]);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        return trim(preg_replace('/[^a-z0-9]+/', ' ', strtolower($value)) ?? '');
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
