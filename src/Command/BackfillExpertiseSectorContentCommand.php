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
    name: 'app:i18n:backfill-expertise-sector-content',
    description: 'Backfill FR expertise, secteur et narratives practice depuis les sources versionnees.',
)]
class BackfillExpertiseSectorContentCommand extends Command
{
    private const EXPERTISE_PAGE_SLUGS = [
        'transformation-si-pme-eti' => 'expertise-transformation-si-pme-eti',
        'amoa-erp-applications-metiers' => 'expertise-amoa-erp-applications-metiers',
        'organisation-processus-conduite-du-changement' => 'expertise-organisation-processus-conduite-du-changement',
        'data-automatisation-intelligence-artificielle' => 'expertise-data-automatisation-intelligence-artificielle',
        'cybersecurite-conformite-resilience' => 'expertise-cybersecurite-conformite-resilience',
        'rgpd-dpo-gouvernance' => 'expertise-rgpd-dpo-gouvernance',
        'amoa-ia-pilotage-projets-agents' => 'expertise-amoa-ia-pilotage-projets-agents',
        'conformite-ia-gouvernance-ai-act' => 'expertise-conformite-ia-gouvernance-ai-act',
        'transformation-digitale-ia-pme-pmi' => 'expertise-transformation-digitale-ia-pme-pmi',
    ];

    private const SECTOR_PAGE_SLUGS = [
        'industrie' => 'secteur-industrie',
        'services' => 'secteur-services',
        'secteur-public' => 'secteur-secteur-public',
    ];

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
            ->addOption('overwrite', null, InputOption::VALUE_NONE, 'Remplace un payload different.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dryRun = (bool) $input->getOption('dry-run');
        $overwrite = (bool) $input->getOption('overwrite');
        $counts = ['write' => 0, 'unchanged' => 0, 'conflict' => 0, 'missing' => 0];
        $report = [];

        foreach ($this->loadSource('expertise_pages.fr.json', 'pages') as $slug => $payload) {
            $this->processSitePage('expertise', $slug, self::EXPERTISE_PAGE_SLUGS[$slug] ?? null, 'expertisePage', $payload, $dryRun, $overwrite, $counts, $report);
        }

        foreach ($this->loadSource('sector_pages.fr.json', 'pages') as $slug => $payload) {
            $this->processSitePage('sector', $slug, self::SECTOR_PAGE_SLUGS[$slug] ?? null, 'sectorPage', $payload, $dryRun, $overwrite, $counts, $report);
        }

        foreach ($this->loadSource('practice_narratives.fr.json', 'narratives') as $slug => $payload) {
            $this->processGlobalContent('practice_narrative', $slug, $payload, $dryRun, $overwrite, $counts, $report);
        }

        $io->table(['Source', 'Target type', 'Target found', 'FR translation found', 'Action'], $report);
        $io->success(sprintf('rows=%d write=%d unchanged=%d conflict=%d missing=%d dry_run=%s overwrite=%s', count($report), $counts['write'], $counts['unchanged'], $counts['conflict'], $counts['missing'], $dryRun ? 'yes' : 'no', $overwrite ? 'yes' : 'no'));

        return $counts['missing'] > 0 || $counts['conflict'] > 0 ? Command::FAILURE : Command::SUCCESS;
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, int> $counts
     * @param array<int, array<int, string>> $report
     */
    private function processSitePage(string $family, string $sourceSlug, ?string $pageSlug, string $payloadKey, array $payload, bool $dryRun, bool $overwrite, array &$counts, array &$report): void
    {
        $row = $pageSlug ? $this->connection->fetchAssociative(
            'SELECT p.id AS page_id, t.id AS translation_id, t.structured_data FROM site_page p LEFT JOIN site_page_translation t ON t.site_page_id = p.id AND t.locale = :locale WHERE p.slug = :slug',
            ['locale' => SitePageTranslation::LOCALE_FR, 'slug' => $pageSlug]
        ) : false;

        if (!is_array($row) || !$row['page_id'] || !$row['translation_id']) {
            ++$counts['missing'];
            $report[] = [$sourceSlug, $family . ':' . ($pageSlug ?? 'NO_SLUG'), is_array($row) && $row['page_id'] ? 'YES' : 'NO', is_array($row) && $row['translation_id'] ? 'YES' : 'NO', 'MISSING'];
            return;
        }

        $structuredData = is_string($row['structured_data'] ?? null) && trim($row['structured_data']) !== ''
            ? json_decode($row['structured_data'], true, 512, JSON_THROW_ON_ERROR)
            : [];
        $existing = is_array($structuredData) ? ($structuredData[$payloadKey] ?? null) : null;

        if ($this->canonicalJson($existing) === $this->canonicalJson($payload)) {
            ++$counts['unchanged'];
            $report[] = [$sourceSlug, $family . ':' . $pageSlug, 'YES', 'YES', 'UNCHANGED'];
            return;
        }

        if ($existing !== null && !$overwrite) {
            ++$counts['conflict'];
            $report[] = [$sourceSlug, $family . ':' . $pageSlug, 'YES', 'YES', 'CONFLICT_SKIP'];
            return;
        }

        ++$counts['write'];
        $report[] = [$sourceSlug, $family . ':' . $pageSlug, 'YES', 'YES', $dryRun ? 'WOULD_WRITE' : ($overwrite ? 'OVERWRITE' : 'WRITE')];
        if ($dryRun) {
            return;
        }

        $structuredData[$payloadKey] = $payload;
        $this->connection->update('site_page_translation', [
            'title' => $payload['title'] ?? $sourceSlug,
            'hero_badge' => $payload['eyebrow'] ?? null,
            'hero_title' => $payload['title'] ?? null,
            'hero_intro' => $payload['intro'] ?? null,
            'seo_title' => $payload['seoTitle'] ?? null,
            'seo_description' => $payload['metaDescription'] ?? null,
            'structured_data' => json_encode($structuredData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ], ['id' => $row['translation_id']]);
    }

    /**
     * @param array<string, mixed> $payload
     * @param array<string, int> $counts
     * @param array<int, array<int, string>> $report
     */
    private function processGlobalContent(string $family, string $slug, array $payload, bool $dryRun, bool $overwrite, array &$counts, array &$report): void
    {
        $identifier = $family . '_' . $slug;
        $created = false;
        $row = $this->connection->fetchAssociative(
            'SELECT g.id AS content_id, t.id AS translation_id, t.body_html FROM site_global_content g LEFT JOIN site_global_content_translation t ON t.site_global_content_id = g.id AND t.locale = :locale WHERE g.identifier = :identifier',
            ['locale' => SitePageTranslation::LOCALE_FR, 'identifier' => $identifier]
        );

        if (!is_array($row) || !$row['content_id']) {
            ++$counts['write'];
            $report[] = [$slug, $family . ':' . $identifier, 'NO', 'NO', $dryRun ? 'WOULD_CREATE' : 'CREATE'];
            if ($dryRun) {
                return;
            }
            $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
            $this->connection->insert('site_global_content', ['identifier' => $identifier, 'enabled' => 1, 'created_at' => $now, 'updated_at' => $now]);
            $row = ['content_id' => $this->connection->lastInsertId(), 'translation_id' => null, 'body_html' => null];
            $created = true;
        }

        $existing = is_string($row['body_html'] ?? null) && trim($row['body_html']) !== ''
            ? json_decode($row['body_html'], true, 512, JSON_THROW_ON_ERROR)
            : null;

        if ($this->canonicalJson($existing) === $this->canonicalJson($payload)) {
            ++$counts['unchanged'];
            $report[] = [$slug, $family . ':' . $identifier, 'YES', $row['translation_id'] ? 'YES' : 'NO', 'UNCHANGED'];
            return;
        }

        if ($existing !== null && !$overwrite) {
            ++$counts['conflict'];
            $report[] = [$slug, $family . ':' . $identifier, 'YES', $row['translation_id'] ? 'YES' : 'NO', 'CONFLICT_SKIP'];
            return;
        }

        if (!$created) {
            ++$counts['write'];
            $report[] = [$slug, $family . ':' . $identifier, 'YES', $row['translation_id'] ? 'YES' : 'NO', $dryRun ? 'WOULD_WRITE' : ($row['translation_id'] ? 'WRITE' : 'CREATE_TRANSLATION')];
        }
        if ($dryRun) {
            return;
        }

        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $body = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($row['translation_id']) {
            $this->connection->update('site_global_content_translation', ['title' => $payload['headline'] ?? $payload['eyebrow'] ?? $slug, 'body_html' => $body, 'updated_at' => $now], ['id' => $row['translation_id']]);
            return;
        }

        $this->connection->insert('site_global_content_translation', [
            'site_global_content_id' => $row['content_id'],
            'locale' => SitePageTranslation::LOCALE_FR,
            'title' => $payload['headline'] ?? $payload['eyebrow'] ?? $slug,
            'body_html' => $body,
            'translation_status' => SitePageTranslation::STATUS_PUBLISHED,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function loadSource(string $file, string $key): array
    {
        $decoded = json_decode((string) file_get_contents($this->kernel->getProjectDir() . '/data/i18n/' . $file), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($decoded) || ($decoded['locale'] ?? null) !== SitePageTranslation::LOCALE_FR || !is_array($decoded[$key] ?? null)) {
            throw new \RuntimeException(sprintf('Invalid source file: %s', $file));
        }

        return $decoded[$key];
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
