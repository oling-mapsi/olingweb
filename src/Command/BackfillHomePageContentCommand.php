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
    name: 'app:i18n:backfill-home-page',
    description: 'Backfill le contenu homepage FR depuis la source data versionnee.',
)]
class BackfillHomePageContentCommand extends Command
{
    private const SOURCE_PATH_PATTERN = '/data/i18n/home_page.%s.json';

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
            ->addOption('locale', null, InputOption::VALUE_REQUIRED, 'Locale source to backfill: fr, en or es.', SitePageTranslation::LOCALE_FR)
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide et affiche les actions sans ecrire en base.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace un contenu homepage existant different.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $locale = (string) $input->getOption('locale');
        SitePageTranslation::assertSupportedLocale($locale);
        $source = $this->loadSource($locale);
        $page = $this->sitePageRepository->findOneBy(['slug' => $source['slug']]);

        if ($page === null) {
            $io->error(sprintf('Missing SitePage "%s".', $source['slug']));
            return Command::FAILURE;
        }

        $translation = $page->getTranslation($locale);
        if (!$translation instanceof SitePageTranslation) {
            $io->error(sprintf('Missing %s translation for SitePage "%s".', strtoupper($locale), $source['slug']));
            return Command::FAILURE;
        }

        $structuredData = $translation->getStructuredData() ?? [];
        $existing = $structuredData['homePage'] ?? null;
        $incoming = $source['homePage'];

        if ($this->canonicalJson($existing) === $this->canonicalJson($incoming)) {
            $io->success(sprintf('homePage %s unchanged dry_run=%s', $locale, $dryRun ? 'yes' : 'no'));
            return Command::SUCCESS;
        }

        if ($existing !== null && !$overwrite) {
            $io->error('Existing different homePage payload. Re-run with --overwrite to replace it.');
            return Command::FAILURE;
        }

        if (!$dryRun) {
            $structuredData['homePage'] = $incoming;
            $translation->setStructuredData($structuredData);
            $this->entityManager->flush();
        }

        $io->success(sprintf('homePage %s %s dry_run=%s', $locale, $existing === null ? 'created' : 'overwritten', $dryRun ? 'yes' : 'no'));

        return Command::SUCCESS;
    }

    /**
     * @return array{slug: string, locale: string, homePage: array<string, mixed>}
     */
    private function loadSource(string $locale): array
    {
        $path = $this->kernel->getProjectDir() . sprintf(self::SOURCE_PATH_PATTERN, $locale);
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['slug'] ?? null) !== 'home' || ($decoded['locale'] ?? null) !== $locale || !is_array($decoded['homePage'] ?? null)) {
            throw new \RuntimeException(sprintf('Invalid homepage source file: %s', $path));
        }

        return $decoded;
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
