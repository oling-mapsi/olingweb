<?php

namespace App\Service;

use App\Dto\SitePagePublicView;
use App\Dto\TranslatedEntityPublicView;
use App\Entity\HomeSection;
use App\Entity\LegalPage;
use App\Entity\Practice;
use App\Entity\Projet;
use App\Entity\Services;
use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Entity\Team;
use App\Repository\LocalizedSlugHistoryRepository;
use App\Repository\SitePageTranslationRepository;
use Doctrine\DBAL\Connection;

class LocalizedContentResolver
{
    public function __construct(
        private readonly SitePageTranslationRepository $sitePageTranslationRepository,
        private readonly LocalizedSlugHistoryRepository $localizedSlugHistoryRepository,
        private readonly Connection $connection,
    ) {
    }

    public function getTranslation(SitePage $page, string $locale): ?SitePageTranslation
    {
        SitePageTranslation::assertSupportedLocale($locale);

        return $page->getTranslation($locale) ?? $this->sitePageTranslationRepository->findOneByPageAndLocale($page, $locale);
    }

    public function getPublishedTranslation(SitePage $page, string $locale): ?SitePageTranslation
    {
        SitePageTranslation::assertSupportedLocale($locale);

        $translation = $page->getPublishedTranslation($locale);

        return $translation ?? $this->sitePageTranslationRepository->findOnePublishedByPageAndLocale($page, $locale);
    }

    public function getFrenchPublicView(SitePage $page): SitePagePublicView
    {
        $translation = $this->getPublishedTranslation($page, SitePageTranslation::LOCALE_FR);
        if (!$translation instanceof SitePageTranslation) {
            throw new \LogicException(sprintf('Missing FR translation for SitePage #%s.', $page->getId() ?? 'new'));
        }

        return new SitePagePublicView($page, $translation);
    }

    public function getPublicView(SitePage $page, string $locale): ?SitePagePublicView
    {
        $translation = $this->getPublishedTranslation($page, $locale);

        return $translation instanceof SitePageTranslation ? new SitePagePublicView($page, $translation) : null;
    }

    public function getPublishedPublicViewByLocalizedSlug(string $locale, string $slug): ?SitePagePublicView
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $translation = $this->sitePageTranslationRepository->findOnePublishedByLocaleAndSlug($locale, $slug);
        $page = $translation?->getSitePage();

        return $page instanceof SitePage ? new SitePagePublicView($page, $translation) : null;
    }

    public function getCurrentSlugFromHistory(string $resourceType, int $resourceId, string $locale, string $oldSlug): ?string
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $history = $this->localizedSlugHistoryRepository->findOneByResourceLocaleAndOldSlug($resourceType, $resourceId, $locale, $oldSlug);

        return $history?->getNewSlug();
    }

    public function getFrenchPracticeView(Practice $practice): TranslatedEntityPublicView
    {
        return $this->getPracticeView($practice, SitePageTranslation::LOCALE_FR);
    }

    public function getPracticeView(Practice $practice, string $locale): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($practice, $this->fetchTranslation('practice_translation', 'practice_id', $practice->getId(), [
            'designation',
            'slug',
            'designationShort' => 'designation_short',
            'h1Title' => 'h1_title',
            'introduction',
            'introductionShort' => 'introduction_short',
            'description',
            'descriptionShort' => 'description_short',
            'tags',
        ], $locale), [
            'getServices' => fn () => array_map(fn (Services $service) => $this->getServiceView($service, $locale), $practice->getServices()->toArray()),
        ]);
    }

    public function getFrenchServiceView(Services $service): TranslatedEntityPublicView
    {
        return $this->getServiceView($service, SitePageTranslation::LOCALE_FR);
    }

    public function getServiceView(Services $service, string $locale): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($service, $this->fetchTranslation('service_translation', 'service_id', $service->getId(), [
            'designation',
            'slug',
            'designationShort' => 'designation_short',
            'introductionShort' => 'introduction_short',
            'description',
            'descriptionShort' => 'description_short',
            'publicNarrative' => 'public_narrative',
        ], $locale), [
            'getPractice' => fn () => $service->getPractice() ? $this->getPracticeView($service->getPractice(), $locale) : null,
            'getProjets' => fn () => array_map(fn (Projet $project) => $this->getProjetView($project, $locale), $service->getProjets()->toArray()),
            'getTeams' => fn () => array_map(fn (Team $team) => $this->getTeamView($team, $locale), $service->getTeams()->toArray()),
        ]);
    }

    public function getFrenchProjetView(Projet $project): TranslatedEntityPublicView
    {
        return $this->getProjetView($project, SitePageTranslation::LOCALE_FR);
    }

    public function getProjetView(Projet $project, string $locale): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($project, $this->fetchTranslation('projet_translation', 'projet_id', $project->getId(), [
            'designation',
            'slug',
            'description',
            'shortDescription' => 'short_description',
            'clientName' => 'client_name',
            'territory',
            'periodLabel' => 'period_label',
        ], $locale), [
            'getServices' => fn () => array_map(fn (Services $service) => $this->getServiceView($service, $locale), $project->getServices()->toArray()),
            'getTeams' => fn () => array_map(fn (Team $team) => $this->getTeamView($team, $locale), $project->getTeams()->toArray()),
        ]);
    }

    public function getFrenchTeamView(Team $team): TranslatedEntityPublicView
    {
        return $this->getTeamView($team, SitePageTranslation::LOCALE_FR);
    }

    public function getTeamView(Team $team, string $locale): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($team, $this->fetchTranslation('team_translation', 'team_id', $team->getId(), [
            'titre',
            'shortcv',
            'publicProfile' => 'public_profile',
        ], $locale), [
            'getServices' => fn () => array_map(fn (Services $service) => $this->getServiceView($service, $locale), $team->getServices()->toArray()),
            'getProjets' => fn () => array_map(fn (Projet $project) => $this->getProjetView($project, $locale), $team->getProjets()->toArray()),
        ]);
    }

    public function getFrenchLegalPageView(LegalPage $page): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($page, $this->fetchTranslation('legal_page_translation', 'legal_page_id', $page->getId(), [
            'slug',
            'title',
            'body',
        ]));
    }

    public function getFrenchHomeSectionView(HomeSection $section): TranslatedEntityPublicView
    {
        return $this->getHomeSectionView($section, SitePageTranslation::LOCALE_FR);
    }

    public function getHomeSectionView(HomeSection $section, string $locale): TranslatedEntityPublicView
    {
        return new TranslatedEntityPublicView($section, $this->fetchTranslation('home_section_translation', 'home_section_id', $section->getId(), [
            'slug',
            'title',
            'eyebrow',
            'intro',
            'ctaLabel' => 'cta_label',
            'ctaLabelSecondary' => 'cta_label_secondary',
        ], $locale));
    }

    /**
     * @param array<int|string, string> $fields
     *
     * @return array<string, mixed>
     */
    private function fetchTranslation(string $table, string $ownerColumn, ?int $ownerId, array $fields, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        if ($ownerId === null) {
            throw new \LogicException(sprintf('Missing owner id for "%s" %s translation.', $table, $locale));
        }

        $row = $this->connection->fetchAssociative(sprintf('SELECT * FROM %s WHERE %s = :id AND locale = :locale', $table, $ownerColumn), [
            'id' => $ownerId,
            'locale' => $locale,
        ]);

        if (!is_array($row)) {
            if ($locale !== SitePageTranslation::LOCALE_FR) {
                return $this->emptyTranslatedFields($fields);
            }

            throw new \LogicException(sprintf('Missing %s translation in "%s" for owner #%d.', $locale, $table, $ownerId));
        }

        $data = [];
        foreach ($fields as $property => $column) {
            if (is_int($property)) {
                $property = $column;
            }
            $value = $row[$column] ?? null;
            if (in_array($column, ['tags', 'public_profile', 'public_narrative'], true) && is_string($value)) {
                $value = json_decode($value, true, 512, JSON_THROW_ON_ERROR);
            }
            $data[(string) $property] = $value;
        }

        return $data;
    }

    /**
     * @param array<int|string, string> $fields
     *
     * @return array<string, mixed>
     */
    private function emptyTranslatedFields(array $fields): array
    {
        $data = [];
        foreach ($fields as $property => $column) {
            if (is_int($property)) {
                $property = $column;
            }
            $data[(string) $property] = null;
        }

        return $data;
    }
}
