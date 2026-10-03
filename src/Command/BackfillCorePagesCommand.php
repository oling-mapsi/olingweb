<?php

namespace App\Command;

use App\Entity\SitePageTranslation;
use App\Entity\SitePage;
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
    name: 'app:i18n:backfill-core-pages',
    description: 'Backfill les pages editoriales core FR depuis la source data versionnee.',
)]
class BackfillCorePagesCommand extends Command
{
    private const SOURCE_PATH = '/data/i18n/core_pages.fr.json';

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
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Valide sans ecrire.')
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace un payload different.')
            ->addOption('create-missing', null, InputOption::VALUE_NONE, 'Cree les SitePage FR explicitement presentes dans la source.')
            ->addOption('slug', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Limite le backfill a un ou plusieurs slugs.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $source = $this->loadSource();
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $createMissing = (bool) $input->getOption('create-missing');
        $onlySlugs = array_filter(array_map('strval', (array) $input->getOption('slug')));
        $counts = ['write' => 0, 'unchanged' => 0, 'conflict' => 0, 'missing' => 0];
        $report = [];

        foreach ($source['pages'] as $slug => $payload) {
            if ($onlySlugs !== [] && !in_array($slug, $onlySlugs, true)) {
                continue;
            }
            $created = false;
            $page = $this->sitePageRepository->findOneBy(['slug' => $slug]);
            $translation = $page?->getTranslation(SitePageTranslation::LOCALE_FR);
            if (!$page || !$translation instanceof SitePageTranslation) {
                if (!$createMissing) {
                    ++$counts['missing'];
                    $report[] = [$slug, $page ? 'YES' : 'NO', $translation ? 'YES' : 'NO', 'MISSING'];
                    continue;
                }

                ++$counts['write'];
                $report[] = [$slug, $page ? 'YES' : 'NO', $translation ? 'YES' : 'NO', $dryRun ? 'WOULD_CREATE' : 'CREATE'];
                if ($dryRun) {
                    continue;
                }

                $page ??= $this->createPage($slug, $payload);
                $translation = $this->createTranslation($page, $slug, $payload);
                $created = true;
            }

            $structuredData = $translation->getStructuredData() ?? [];
            $existing = $structuredData['corePage'] ?? null;
            if ($this->canonicalJson($existing) === $this->canonicalJson($payload)) {
                ++$counts['unchanged'];
                $report[] = [$slug, 'YES', 'YES', 'UNCHANGED'];
                continue;
            }

            if ($existing !== null && !$overwrite) {
                ++$counts['conflict'];
                $report[] = [$slug, 'YES', 'YES', 'CONFLICT_SKIP'];
                continue;
            }

            if (!$created) {
                ++$counts['write'];
                $report[] = [$slug, 'YES', 'YES', $dryRun ? 'WOULD_WRITE' : ($overwrite ? 'OVERWRITE' : 'WRITE')];
            }
            if (!$dryRun) {
                $structuredData['corePage'] = $payload;
                $translation->setStructuredData($structuredData);
            }
        }

        $io->table(['Source key', 'SitePage', 'FR Translation', 'Action'], $report);
        if (!$dryRun) {
            $this->entityManager->flush();
        }
        $io->success(sprintf('rows=%d write=%d unchanged=%d conflict=%d missing=%d dry_run=%s overwrite=%s create_missing=%s', count($source['pages']), $counts['write'], $counts['unchanged'], $counts['conflict'], $counts['missing'], $dryRun ? 'yes' : 'no', $overwrite ? 'yes' : 'no', $createMissing ? 'yes' : 'no'));

        return $counts['missing'] > 0 || $counts['conflict'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createPage(string $slug, array $payload): SitePage
    {
        $page = (new SitePage())
            ->setSlug($slug)
            ->setTitle((string) ($payload['title'] ?? $slug))
            ->setMetaDescription($payload['metaDescription'] ?? null)
            ->setHeroBadge($payload['eyebrow'] ?? null)
            ->setHeroTitle($payload['title'] ?? null)
            ->setHeroIntro($payload['intro'] ?? null)
            ->setPublicationStatus('published')
            ->setPublishedAt(new \DateTimeImmutable());
        $this->entityManager->persist($page);

        return $page;
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function createTranslation(SitePage $page, string $slug, array $payload): SitePageTranslation
    {
        $translation = (new SitePageTranslation())
            ->setSitePage($page)
            ->setLocale(SitePageTranslation::LOCALE_FR)
            ->setSlug($slug)
            ->setTitle((string) ($payload['title'] ?? $slug))
            ->setHeroBadge($payload['eyebrow'] ?? null)
            ->setHeroTitle($payload['title'] ?? null)
            ->setHeroIntro($payload['intro'] ?? null)
            ->setSeoTitle($payload['seoTitle'] ?? null)
            ->setSeoDescription($payload['metaDescription'] ?? null)
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable());
        $page->addTranslation($translation);
        $this->entityManager->persist($translation);

        return $translation;
    }

    /**
     * @return array{locale: string, pages: array<string, array<string, mixed>>}
     */
    private function loadSource(): array
    {
        $path = $this->kernel->getProjectDir() . self::SOURCE_PATH;
        $decoded = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['locale'] ?? null) !== SitePageTranslation::LOCALE_FR || !is_array($decoded['pages'] ?? null)) {
            throw new \RuntimeException(sprintf('Invalid core pages source file: %s', $path));
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
