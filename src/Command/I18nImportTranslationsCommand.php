<?php

namespace App\Command;

use App\Service\I18n\SitePageTranslationSnapshotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:i18n:import-translations',
    description: 'Import site page translation JSON snapshots idempotently.',
)]
final class I18nImportTranslationsCommand extends Command
{
    public function __construct(private readonly SitePageTranslationSnapshotService $snapshotService)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Target locale: en or es.')
            ->addOption('input', null, InputOption::VALUE_REQUIRED, 'Input file. Defaults to data/i18n/reviewed/site_pages.{locale}.json.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Read and compare without persisting.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing differing translations.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = (string) $input->getOption('locale');
        $result = $this->snapshotService->import(
            $locale,
            $input->getOption('input') ?: null,
            (bool) $input->getOption('dry-run'),
            (bool) $input->getOption('overwrite')
        );

        foreach ($result['conflicts'] as $conflict) {
            $io->warning($conflict);
        }

        $io->success(sprintf(
            'rows=%d created=%d updated=%d unchanged=%d conflict=%d dry_run=%s',
            $result['rows'],
            $result['created'],
            $result['updated'],
            $result['unchanged'],
            $result['conflict'],
            $result['dryRun'] ? 'yes' : 'no'
        ));

        return $result['conflict'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
