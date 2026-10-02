<?php

namespace App\Command;

use App\Service\I18n\SitePageTranslationSnapshotService;
use App\Service\I18n\EntityTranslationSnapshotService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:i18n:export-translations',
    description: 'Export reviewed or published site page translations to reproducible JSON snapshots.',
)]
final class I18nExportTranslationsCommand extends Command
{
    public function __construct(
        private readonly SitePageTranslationSnapshotService $snapshotService,
        private readonly EntityTranslationSnapshotService $entitySnapshotService,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Target locale: en or es.')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Entity type: SitePage, service or practice.', 'SitePage')
            ->addOption('status', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Export only this status. Repeatable.')
            ->addOption('output', null, InputOption::VALUE_REQUIRED, 'Output file. Defaults to data/i18n/reviewed/site_pages.{locale}.json.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = (string) $input->getOption('locale');
        $entity = (string) $input->getOption('entity');
        $statuses = $input->getOption('status');
        if ($entity === 'SitePage') {
            $payload = $this->snapshotService->export($locale, is_array($statuses) ? $statuses : []);
            $path = $this->snapshotService->writeExport($payload, $input->getOption('output') ?: null);
        } else {
            $payload = $this->entitySnapshotService->export($entity, $locale, is_array($statuses) ? $statuses : []);
            $path = $this->entitySnapshotService->writeExport($entity, $payload, $input->getOption('output') ?: null);
        }
        $count = is_array($payload['translations'] ?? null) ? count($payload['translations']) : 0;

        $io->success(sprintf('exported=%d locale=%s file=%s', $count, $locale, $path));

        return Command::SUCCESS;
    }
}
