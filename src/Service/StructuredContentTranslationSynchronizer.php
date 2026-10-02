<?php

namespace App\Service;

use App\Dto\StructuredContentAdminFormData;
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

    public function practiceFormData(Practice $practice): StructuredContentAdminFormData
    {
        $row = $this->fetch('practice_translation', 'practice_id', $practice->getId());
        $data = new StructuredContentAdminFormData();
        $data->setDesignation($row['designation'] ?? $practice->getDesignation());
        $data->setDesignationShort($row['designation_short'] ?? $practice->getDesignationShort());
        $data->setH1Title($row['h1_title'] ?? $practice->getH1Title());
        $data->setIntroduction($row['introduction'] ?? $practice->getIntroduction());
        $data->setIntroductionShort($row['introduction_short'] ?? $practice->getIntroductionShort());
        $data->setDescription($row['description'] ?? $practice->getDescription());
        $data->setDescriptionShort($row['description_short'] ?? $practice->getDescriptionShort());
        $tags = $row['tags'] ?? null;
        $data->setTags(is_string($tags) ? json_decode($tags, true, 512, JSON_THROW_ON_ERROR) : $practice->getTags());
        $data->setFeaturedHome($practice->isFeaturedHome());
        $data->setClass1($practice->getClass1());
        $data->setColor($practice->getColor());
        $data->setIco($practice->getIco());
        $data->setImage1($practice->getImage1());
        $data->setImage2($practice->getImage2());

        return $data;
    }

    public function savePracticeFromForm(Practice $practice, StructuredContentAdminFormData $data): void
    {
        $practice->setFeaturedHome($data->isFeaturedHome());
        $practice->setClass1($data->getClass1());
        $practice->setColor($data->getColor());
        $practice->setIco($data->getIco());
        $practice->setImage1($data->getImage1());
        $practice->setImage2($data->getImage2());
        $this->upsert('practice_translation', 'practice_id', $practice->getId(), [
            'designation' => $data->getDesignation(),
            'slug' => $practice->getSlug(),
            'designation_short' => $data->getDesignationShort(),
            'h1_title' => $data->getH1Title(),
            'introduction' => $data->getIntroduction(),
            'introduction_short' => $data->getIntroductionShort(),
            'description' => $data->getDescription(),
            'description_short' => $data->getDescriptionShort(),
            'tags' => json_encode($data->getTags(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
        ]);
        $this->syncPracticeTranslationToLegacy($practice);
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

    public function syncPracticeTranslationToLegacy(Practice $practice): void
    {
        $row = $this->fetchRequired('practice_translation', 'practice_id', $practice->getId());
        $practice
            ->setDesignation((string) $row['designation'])
            ->setDesignationShort($row['designation_short'])
            ->setH1Title($row['h1_title'])
            ->setIntroduction($row['introduction'])
            ->setIntroductionShort($row['introduction_short'])
            ->setDescription($row['description'])
            ->setDescriptionShort($row['description_short'])
            ->setTags(is_string($row['tags'] ?? null) ? json_decode($row['tags'], true, 512, JSON_THROW_ON_ERROR) : []);
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

    public function serviceFormData(Services $service): StructuredContentAdminFormData
    {
        $row = $this->fetch('service_translation', 'service_id', $service->getId());
        $data = new StructuredContentAdminFormData();
        $data->setDesignation($row['designation'] ?? $service->getDesignation());
        $data->setDesignationShort($row['designation_short'] ?? $service->getDesignationShort());
        $data->setIntroductionShort($row['introduction_short'] ?? $service->getIntroductionShort());
        $data->setDescription($row['description'] ?? $service->getDescription());
        $data->setDescriptionShort($row['description_short'] ?? $service->getDescriptionShort());
        $data->setPractice($service->getPractice());
        $data->setIco($service->getIco());
        $data->setImage1($service->getImage1());
        $data->setImage2($service->getImage2());
        $data->setImageHero($service->getImageHero());
        $data->setTeams($service->getTeams());

        return $data;
    }

    public function saveServiceFromForm(Services $service, StructuredContentAdminFormData $data): void
    {
        $service->setPractice($data->getPractice());
        $service->setIco($data->getIco());
        $service->setImage1($data->getImage1());
        $service->setImage2($data->getImage2());
        $service->setImageHero($data->getImageHero());
        foreach ($service->getTeams()->toArray() as $team) {
            $service->removeTeam($team);
        }
        foreach ($data->getTeams() ?? [] as $team) {
            $service->addTeam($team);
        }
        $this->upsert('service_translation', 'service_id', $service->getId(), [
            'designation' => $data->getDesignation(),
            'slug' => $service->getSlug(),
            'designation_short' => $data->getDesignationShort(),
            'introduction_short' => $data->getIntroductionShort(),
            'description' => $data->getDescription(),
            'description_short' => $data->getDescriptionShort(),
        ]);
        $this->syncServiceTranslationToLegacy($service);
    }

    public function syncServiceTranslationToLegacy(Services $service): void
    {
        $row = $this->fetchRequired('service_translation', 'service_id', $service->getId());
        $service
            ->setDesignation((string) $row['designation'])
            ->setDesignationShort($row['designation_short'])
            ->setIntroductionShort($row['introduction_short'])
            ->setDescription($row['description'])
            ->setDescriptionShort($row['description_short']);
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

    public function projetFormData(Projet $project): StructuredContentAdminFormData
    {
        $row = $this->fetch('projet_translation', 'projet_id', $project->getId());
        $data = new StructuredContentAdminFormData();
        $data->setDesignation($row['designation'] ?? $project->getDesignation());
        $data->setDescription($row['description'] ?? $project->getDescription());
        $data->setShortDescription($row['short_description'] ?? $project->getShortDescription());
        $data->setClientName($row['client_name'] ?? $project->getClientName());
        $data->setTerritory($row['territory'] ?? $project->getTerritory());
        $data->setPeriodLabel($row['period_label'] ?? $project->getPeriodLabel());
        $data->setPublicUrl($project->getPublicUrl());
        $data->setClass($project->getClass());
        $data->setMetier($project->getMetier());
        $data->setServices($project->getServices());
        $data->setTeams($project->getTeams());
        $data->setFeaturedProjects($project->isFeaturedProjects());
        $data->setImage($project->getImage());
        $data->setImageHero($project->getImageHero());

        return $data;
    }

    public function saveProjetFromForm(Projet $project, StructuredContentAdminFormData $data): void
    {
        $project->setPublicUrl($data->getPublicUrl());
        $project->setClass($data->getClass());
        $project->setMetier($data->getMetier());
        $project->setFeaturedProjects($data->isFeaturedProjects());
        $project->setImage($data->getImage());
        $project->setImageHero($data->getImageHero());
        foreach ($project->getServices()->toArray() as $service) {
            $project->removeService($service);
        }
        foreach ($data->getServices() ?? [] as $service) {
            $project->addService($service);
        }
        foreach ($project->getTeams()->toArray() as $team) {
            $project->removeTeam($team);
        }
        foreach ($data->getTeams() ?? [] as $team) {
            $project->addTeam($team);
        }
        $this->upsert('projet_translation', 'projet_id', $project->getId(), [
            'designation' => $data->getDesignation(),
            'slug' => $project->getSlug(),
            'description' => $data->getDescription(),
            'short_description' => $data->getShortDescription(),
            'client_name' => $data->getClientName(),
            'territory' => $data->getTerritory(),
            'period_label' => $data->getPeriodLabel(),
        ]);
        $this->syncProjetTranslationToLegacy($project);
    }

    public function syncProjetTranslationToLegacy(Projet $project): void
    {
        $row = $this->fetchRequired('projet_translation', 'projet_id', $project->getId());
        $project
            ->setDesignation((string) $row['designation'])
            ->setDescription($row['description'])
            ->setShortDescription($row['short_description'])
            ->setClientName($row['client_name'])
            ->setTerritory($row['territory'])
            ->setPeriodLabel($row['period_label']);
    }

    public function syncTeam(Team $team): void
    {
        $this->upsert('team_translation', 'team_id', $team->getId(), [
            'titre' => $team->getTitre(),
            'shortcv' => $team->getShortcv(),
        ]);
    }

    public function teamFormData(Team $team): StructuredContentAdminFormData
    {
        $row = $this->fetch('team_translation', 'team_id', $team->getId());
        $data = new StructuredContentAdminFormData();
        $data->setNoncomplet($team->getNoncomplet());
        $data->setTitre($row['titre'] ?? $team->getTitre());
        $data->setShortcv($row['shortcv'] ?? $team->getShortcv());
        $data->setLinkedin($team->getLinkedin());
        $data->setPhoto($team->getPhoto());
        $data->setServices($team->getServices());

        return $data;
    }

    public function saveTeamFromForm(Team $team, StructuredContentAdminFormData $data): void
    {
        $team->setNoncomplet((string) $data->getNoncomplet());
        $team->setLinkedin($data->getLinkedin());
        $team->setPhoto($data->getPhoto());
        foreach ($team->getServices()->toArray() as $service) {
            $team->removeService($service);
        }
        foreach ($data->getServices() ?? [] as $service) {
            $team->addService($service);
        }
        $this->upsert('team_translation', 'team_id', $team->getId(), [
            'titre' => $data->getTitre(),
            'shortcv' => $data->getShortcv(),
        ]);
        $this->syncTeamTranslationToLegacy($team);
    }

    public function syncTeamTranslationToLegacy(Team $team): void
    {
        $row = $this->fetchRequired('team_translation', 'team_id', $team->getId());
        $team->setTitre($row['titre'])->setShortcv($row['shortcv']);
    }

    public function syncLegalPage(LegalPage $page): void
    {
        $this->upsert('legal_page_translation', 'legal_page_id', $page->getId(), [
            'slug' => $page->getSlug(),
            'title' => $page->getTitle(),
            'body' => $page->getBody(),
        ]);
    }

    public function legalPageFormData(LegalPage $page): StructuredContentAdminFormData
    {
        $row = $this->fetch('legal_page_translation', 'legal_page_id', $page->getId());
        $data = new StructuredContentAdminFormData();
        $data->setTitle($row['title'] ?? $page->getTitle());
        $data->setBody($row['body'] ?? $page->getBody());

        return $data;
    }

    public function saveLegalPageFromForm(LegalPage $page, StructuredContentAdminFormData $data): void
    {
        $this->upsert('legal_page_translation', 'legal_page_id', $page->getId(), [
            'slug' => $page->getSlug(),
            'title' => $data->getTitle(),
            'body' => $data->getBody(),
        ]);
        $this->syncLegalPageTranslationToLegacy($page);
    }

    public function syncLegalPageTranslationToLegacy(LegalPage $page): void
    {
        $row = $this->fetchRequired('legal_page_translation', 'legal_page_id', $page->getId());
        $page->setTitle($row['title'])->setBody($row['body']);
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

    public function homeSectionFormData(HomeSection $section): StructuredContentAdminFormData
    {
        $row = $this->fetch('home_section_translation', 'home_section_id', $section->getId());
        $data = new StructuredContentAdminFormData();
        $data->setTitle($row['title'] ?? $section->getTitle());
        $data->setEyebrow($row['eyebrow'] ?? $section->getEyebrow());
        $data->setIntro($row['intro'] ?? $section->getIntro());
        $data->setCtaLabel($row['cta_label'] ?? $section->getCtaLabel());
        $data->setCtaUrl($section->getCtaUrl());

        return $data;
    }

    public function saveHomeSectionFromForm(HomeSection $section, StructuredContentAdminFormData $data): void
    {
        $section->setCtaUrl($data->getCtaUrl());
        $this->upsert('home_section_translation', 'home_section_id', $section->getId(), [
            'slug' => $section->getSlug(),
            'title' => $data->getTitle(),
            'eyebrow' => $data->getEyebrow(),
            'intro' => $data->getIntro(),
            'cta_label' => $data->getCtaLabel(),
            'cta_label_secondary' => $section->getCtaLabelSecondary(),
        ]);
        $this->syncHomeSectionTranslationToLegacy($section);
    }

    public function syncHomeSectionTranslationToLegacy(HomeSection $section): void
    {
        $row = $this->fetchRequired('home_section_translation', 'home_section_id', $section->getId());
        $section
            ->setTitle($row['title'])
            ->setEyebrow($row['eyebrow'])
            ->setIntro($row['intro'])
            ->setCtaLabel($row['cta_label'])
            ->setCtaLabelSecondary($row['cta_label_secondary']);
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

    private function fetch(string $table, string $ownerColumn, ?int $ownerId): ?array
    {
        if ($ownerId === null) {
            return null;
        }

        $row = $this->connection->fetchAssociative(sprintf('SELECT * FROM %s WHERE %s = :id AND locale = :locale', $table, $ownerColumn), [
            'id' => $ownerId,
            'locale' => SitePageTranslation::LOCALE_FR,
        ]);

        return is_array($row) ? $row : null;
    }

    private function fetchRequired(string $table, string $ownerColumn, ?int $ownerId): array
    {
        $row = $this->fetch($table, $ownerColumn, $ownerId);
        if ($row === null) {
            throw new \LogicException(sprintf('Missing FR translation in "%s" for owner #%s.', $table, $ownerId ?? 'new'));
        }

        return $row;
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
