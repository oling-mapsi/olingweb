<?php

namespace App\Service;

use App\Entity\HomeSection;
use App\Entity\LegalPage;
use App\Entity\Practice;
use App\Entity\Projet;
use App\Entity\Services;
use App\Entity\SitePageTranslation;
use App\Entity\Team;
use Doctrine\DBAL\Connection;

final class StructuredContentTranslationSynchronizer
{
    public function __construct(private readonly Connection $connection)
    {
    }

    public function syncPractice(Practice $practice): void
    {
        $this->upsert('practice_translation', 'practice_id', $practice->getId(), [
            'designation' => $practice->getDesignation(),
            'slug' => $practice->getSlug(),
            'designation_short' => $practice->getDesignationShort(),
            'h1_title' => $practice->getH1Title(),
            'introduction' => $practice->getIntroduction(),
            'introduction_short' => $practice->getIntroductionShort(),
            'description' => $practice->getDescription(),
            'description_short' => $practice->getDescriptionShort(),
            'tags' => json_encode($practice->getTags(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ]);
    }

    public function syncService(Services $service): void
    {
        $this->upsert('service_translation', 'service_id', $service->getId(), [
            'designation' => $service->getDesignation(),
            'slug' => $service->getSlug(),
            'designation_short' => $service->getDesignationShort(),
            'introduction_short' => $service->getIntroductionShort(),
            'description' => $service->getDescription(),
            'description_short' => $service->getDescriptionShort(),
        ]);
    }

    public function syncProjet(Projet $project): void
    {
        $this->upsert('projet_translation', 'projet_id', $project->getId(), [
            'designation' => $project->getDesignation(),
            'slug' => $project->getSlug(),
            'description' => $project->getDescription(),
            'short_description' => $project->getShortDescription(),
            'client_name' => $project->getClientName(),
            'territory' => $project->getTerritory(),
            'period_label' => $project->getPeriodLabel(),
        ]);
    }

    public function syncTeam(Team $team): void
    {
        $this->upsert('team_translation', 'team_id', $team->getId(), [
            'titre' => $team->getTitre(),
            'shortcv' => $team->getShortcv(),
        ]);
    }

    public function syncLegalPage(LegalPage $page): void
    {
        $this->upsert('legal_page_translation', 'legal_page_id', $page->getId(), [
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
            'body' => $page->getBody(),
        ]);
    }

    public function syncHomeSection(HomeSection $section): void
    {
        $this->upsert('home_section_translation', 'home_section_id', $section->getId(), [
            'slug' => $section->getSlug(),
            'title' => $section->getTitle(),
            'eyebrow' => $section->getEyebrow(),
            'intro' => $section->getIntro(),
            'cta_label' => $section->getCtaLabel(),
            'cta_label_secondary' => $section->getCtaLabelSecondary(),
        ]);
    }

    /**
     * @param array<string, mixed> $fields
     */
    private function upsert(string $table, string $ownerColumn, ?int $ownerId, array $fields): void
    {
        if ($ownerId === null) {
            return;
        }

        $oldSlug = null;
        if (array_key_exists('slug', $fields)) {
            $oldSlug = $this->connection->fetchOne(sprintf('SELECT slug FROM %s WHERE %s = :id AND locale = :locale', $table, $ownerColumn), [
                'id' => $ownerId,
                'locale' => SitePageTranslation::LOCALE_FR,
            ]);
        }

        $fields['translation_status'] = SitePageTranslation::STATUS_PUBLISHED;
        $fields['source_content_hash'] = hash('sha256', json_encode($fields, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        $fields['source_updated_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        $fields['updated_at'] = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

        $insert = array_merge([
            $ownerColumn => $ownerId,
            'locale' => SitePageTranslation::LOCALE_FR,
            'created_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
        ], $fields);

        $columns = array_keys($insert);
        $updates = array_filter($columns, static fn (string $column): bool => !in_array($column, [$ownerColumn, 'locale', 'created_at'], true));

        $this->connection->executeStatement(sprintf(
            'INSERT INTO %s (%s) VALUES (%s) ON DUPLICATE KEY UPDATE %s',
            $table,
            implode(', ', $columns),
            implode(', ', array_map(static fn (string $column): string => ':' . $column, $columns)),
            implode(', ', array_map(static fn (string $column): string => $column . ' = VALUES(' . $column . ')', $updates))
        ), $insert);

        $newSlug = $fields['slug'] ?? null;
        if (is_string($oldSlug) && is_string($newSlug) && $oldSlug !== '' && $newSlug !== '' && $oldSlug !== $newSlug) {
            $this->connection->insert('localized_slug_history', [
                'resource_type' => $this->resourceTypeForTable($table),
                'resource_id' => $ownerId,
                'locale' => SitePageTranslation::LOCALE_FR,
                'old_slug' => $oldSlug,
                'new_slug' => $newSlug,
                'changed_at' => (new \DateTimeImmutable())->format('Y-m-d H:i:s'),
            ]);
        }
    }

    private function resourceTypeForTable(string $table): string
    {
        return match ($table) {
            'practice_translation' => 'practice',
            'service_translation' => 'service',
            'projet_translation' => 'project',
            'legal_page_translation' => 'legal_page',
            'home_section_translation' => 'home_section',
            default => $table,
        };
    }
}
