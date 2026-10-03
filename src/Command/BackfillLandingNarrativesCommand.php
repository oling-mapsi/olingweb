<?php

namespace App\Command;

use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;

#[AsCommand(
    name: 'app:i18n:backfill-landing-narratives',
    description: 'Backfill les narratives SEO FR depuis la source data versionnee.',
)]
class BackfillLandingNarrativesCommand extends Command
{
    private const SOURCE_PATH = '/data/i18n/landing_narratives.fr.json';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly KernelInterface $kernel,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide et affiche les actions sans ecrire en base.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace une narrative existante differente.')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limite le backfill a un ou plusieurs slugs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $onlySlugs = array_filter(array_map('strval', (array) $input->getOption('slug')));
        $rows = $this->loadRows();
        $summary = [
            'created' => 0,
            'unchanged' => 0,
            'conflict' => 0,
            'missing_page' => 0,
            'missing_fr_translation' => 0,
        ];
        $report = [];

        foreach ($rows as $row) {
            $slug = (string) $row['slug'];
            if ($onlySlugs !== [] && !in_array($slug, $onlySlugs, true)) {
                continue;
            }
            $page = $this->sitePageRepository->findOneBy(['slug' => $slug]);
            if ($page === null) {
                ++$summary['missing_page'];
                $report[] = [$slug, 'NO', 'NO', 'NO', 'MISSING_PAGE'];
                continue;
            }

            $translation = $page->getTranslation(SitePageTranslation::LOCALE_FR);
            if (!$translation instanceof SitePageTranslation) {
                ++$summary['missing_fr_translation'];
                $report[] = [$slug, 'YES', 'NO', 'NO', 'MISSING_FR_TRANSLATION'];
                continue;
            }

            $structuredData = $translation->getStructuredData() ?? [];
            $existing = $structuredData['landingNarrative'] ?? null;
            $incoming = $row['narrative'];

            if ($existing === $incoming) {
                ++$summary['unchanged'];
                $report[] = [$slug, 'YES', 'YES', 'YES', 'UNCHANGED'];
                continue;
            }

            if ($existing !== null && !$overwrite) {
                ++$summary['conflict'];
                $report[] = [$slug, 'YES', 'YES', 'YES', 'CONFLICT_SKIP'];
                continue;
            }

            ++$summary['created'];
            $report[] = [$slug, 'YES', 'YES', $existing === null ? 'NO' : 'YES', $dryRun ? 'WOULD_WRITE' : ($overwrite ? 'OVERWRITE' : 'WRITE')];

            if (!$dryRun) {
                $structuredData['landingNarrative'] = $incoming;
                $translation->setStructuredData($structuredData);
            }
        }

        $io->table(['Source key', 'SitePage', 'FR Translation', 'Existing narrative', 'Action'], $report);

        if (!$dryRun) {
            $this->entityManager->flush();
        }

        $io->success(sprintf(
            'rows=%d created=%d unchanged=%d conflict=%d missing_page=%d missing_fr_translation=%d dry_run=%s overwrite=%s',
            count($rows),
            $summary['created'],
            $summary['unchanged'],
            $summary['conflict'],
            $summary['missing_page'],
            $summary['missing_fr_translation'],
            $dryRun ? 'yes' : 'no',
            $overwrite ? 'yes' : 'no'
        ));

        return $summary['missing_page'] > 0 || $summary['missing_fr_translation'] > 0 || $summary['conflict'] > 0
            ? Command::FAILURE
            : Command::SUCCESS;
    }

    /**
     * @return array<int, array{slug: string, locale: string, narrative: array<string, mixed>}>
     */
    private function loadRows(): array
    {
        $path = $this->kernel->getProjectDir() . self::SOURCE_PATH;
        if (!is_file($path)) {
            throw new \RuntimeException(sprintf('Missing landing narrative source file: %s', $path));
        }

        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded)) {
            throw new \RuntimeException('Landing narrative source file must contain a JSON array.');
        }

        foreach ($decoded as $index => $row) {
            if (!is_array($row) || !isset($row['slug'], $row['locale'], $row['narrative']) || $row['locale'] !== SitePageTranslation::LOCALE_FR || !is_array($row['narrative'])) {
                throw new \RuntimeException(sprintf('Invalid landing narrative row at index %d.', $index));
            }
        }

        return $decoded;
    }
}
