<?php

namespace App\Command;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Service\I18n\AiTranslationService;
use App\Service\I18n\EntityAiTranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:i18n:translate',
    description: 'Generate controlled AI translations for localized content without publishing them.',
)]
final class I18nTranslateCommand extends Command
{
    public function __construct(
        private readonly SitePageRepository $sitePageRepository,
        private readonly AiTranslationService $translationService,
        private readonly EntityAiTranslationService $entityTranslationService,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Target locale: en or es.')
            ->addOption('entity', null, InputOption::VALUE_REQUIRED, 'Entity type: SitePage, service, practice, project or team.', 'SitePage')
            ->addOption('id', null, InputOption::VALUE_REQUIRED, 'Filter by entity id.')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED, 'Filter by source FR slug.')
            ->addOption('status', null, InputOption::VALUE_REQUIRED, 'Filter by existing target translation status.')
            ->addOption('only-missing', null, InputOption::VALUE_NONE, 'Only generate missing translations.')
            ->addOption('only-outdated', null, InputOption::VALUE_NONE, 'Only regenerate outdated translations.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Overwrite existing translations. Output status remains ai_translated.')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Plan work without calling AI or persisting.')
            ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Maximum number of rows to process.', '10');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $locale = (string) $input->getOption('locale');
        $entity = (string) $input->getOption('entity');
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $onlyMissing = (bool) $input->getOption('only-missing');
        $onlyOutdated = (bool) $input->getOption('only-outdated');
        $limit = max(1, (int) $input->getOption('limit'));

        if (!in_array($locale, [SitePageTranslation::LOCALE_EN, SitePageTranslation::LOCALE_ES], true)) {
            $io->error('Use --locale=en or --locale=es.');
            return Command::FAILURE;
        }

        if ($entity !== 'SitePage') {
            return $this->executeDbEntity($input, $io, $entity, $locale, $limit, $dryRun, $overwrite, $onlyMissing, $onlyOutdated);
        }

        $pages = $this->selectSitePages($input, $limit);
        $rows = [];
        $counts = ['generated' => 0, 'skipped' => 0, 'conflict' => 0, 'error' => 0];

        foreach ($pages as $page) {
            $plan = $this->translationService->planSitePage($page, $locale, $overwrite, $onlyMissing, $onlyOutdated);
            if ($input->getOption('status') && $plan['currentStatus'] !== $input->getOption('status')) {
                continue;
            }

            $rows[] = [
                $plan['entity'],
                (string) ($plan['id'] ?? ''),
                (string) ($plan['slug'] ?? ''),
                $plan['locale'],
                implode(',', $plan['fields']),
                $plan['currentStatus'] ?? 'missing',
                $plan['outdated'] ? 'yes' : 'no',
                $plan['action'],
            ];

            if ($dryRun || str_starts_with($plan['action'], 'skip')) {
                ++$counts['skipped'];
                continue;
            }

            try {
                $this->translationService->translateSitePage($page, $locale, $overwrite, $onlyMissing, $onlyOutdated);
                ++$counts['generated'];
            } catch (\App\Service\I18n\AiTranslationConflictException $exception) {
                ++$counts['conflict'];
                $io->warning($exception->getMessage());
            } catch (\Throwable $exception) {
                ++$counts['error'];
                $io->error($exception->getMessage());
            }
        }

        $io->table(['source', 'id', 'slug', 'target', 'fields', 'current status', 'outdated', 'action'], $rows);
        if (!$dryRun) {
            $this->entityManager->flush();
        }
        $io->success(sprintf(
            'rows=%d generated=%d skipped=%d conflict=%d error=%d dry_run=%s',
            count($rows),
            $counts['generated'],
            $counts['skipped'],
            $counts['conflict'],
            $counts['error'],
            $dryRun ? 'yes' : 'no'
        ));

        return ($counts['conflict'] > 0 || $counts['error'] > 0) ? Command::FAILURE : Command::SUCCESS;
    }

    private function executeDbEntity(InputInterface $input, SymfonyStyle $io, string $entity, string $locale, int $limit, bool $dryRun, bool $overwrite, bool $onlyMissing, bool $onlyOutdated): int
    {
        if (!$this->entityTranslationService->supports($entity)) {
            $io->error('Use --entity=SitePage, --entity=service, --entity=practice, --entity=project or --entity=team.');
            return Command::FAILURE;
        }

        $sourceLimit = $input->getOption('id') || $input->getOption('slug') ? 1 : 500;
        $sources = $this->entityTranslationService->selectSources(
            $entity,
            $input->getOption('id') ? (int) $input->getOption('id') : null,
            $input->getOption('slug') ? (string) $input->getOption('slug') : null,
            $sourceLimit
        );
        $rows = [];
        $counts = ['generated' => 0, 'skipped' => 0, 'conflict' => 0, 'error' => 0];

        foreach ($sources as $source) {
            $plan = $this->entityTranslationService->plan($entity, $source, $locale, $overwrite, $onlyMissing, $onlyOutdated);
            if ($input->getOption('status') && $plan['currentStatus'] !== $input->getOption('status')) {
                continue;
            }
            if (!$dryRun && !str_starts_with($plan['action'], 'skip') && $counts['generated'] >= $limit) {
                continue;
            }

            $rows[] = [$plan['entity'], (string) $plan['id'], $plan['slug'], $plan['locale'], implode(',', $plan['fields']), $plan['currentStatus'] ?? 'missing', $plan['outdated'] ? 'yes' : 'no', $plan['action']];
            if ($dryRun || str_starts_with($plan['action'], 'skip')) {
                ++$counts['skipped'];
                continue;
            }

            try {
                $this->entityTranslationService->translate($entity, $source, $locale, $overwrite, $onlyMissing, $onlyOutdated);
                ++$counts['generated'];
            } catch (\App\Service\I18n\AiTranslationConflictException $exception) {
                ++$counts['conflict'];
                $io->warning($exception->getMessage());
            } catch (\Throwable $exception) {
                ++$counts['error'];
                $io->error($exception->getMessage());
            }
        }

        $io->table(['source', 'id', 'slug', 'target', 'fields', 'current status', 'outdated', 'action'], $rows);
        $io->success(sprintf('rows=%d generated=%d skipped=%d conflict=%d error=%d dry_run=%s', count($rows), $counts['generated'], $counts['skipped'], $counts['conflict'], $counts['error'], $dryRun ? 'yes' : 'no'));

        return ($counts['conflict'] > 0 || $counts['error'] > 0) ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @return SitePage[]
     */
    private function selectSitePages(InputInterface $input, int $limit): array
    {
        if ($input->getOption('id')) {
            $page = $this->sitePageRepository->find((int) $input->getOption('id'));
            return $page instanceof SitePage ? [$page] : [];
        }

        if ($input->getOption('slug')) {
            $page = $this->sitePageRepository->findOneBy(['slug' => (string) $input->getOption('slug')]);
            return $page instanceof SitePage ? [$page] : [];
        }

        return $this->sitePageRepository->findBy([], ['slug' => 'ASC'], $limit);
    }
}
