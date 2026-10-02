<?php

namespace App\Service\I18n;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\TranslationSourceHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class SitePageTranslationSnapshotService
{
    private const DEFAULT_DIR = '/data/i18n/reviewed';

    public function __construct(
        private readonly SitePageRepository $sitePageRepository,
        private readonly SitePageTranslationRepository $translationRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly TranslationSourceHasher $sourceHasher,
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * @param string[] $statuses
     * @return array<string, mixed>
     */
    public function export(string $locale, array $statuses = []): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        if ($locale === SitePageTranslation::LOCALE_FR) {
            throw new \InvalidArgumentException('FR is the source locale and is not exported by this command.');
        }

        $criteria = ['locale' => $locale];
        if ($statuses !== []) {
            foreach ($statuses as $status) {
                SitePageTranslation::assertSupportedStatus($status);
            }
            $criteria['translationStatus'] = $statuses;
        }

        $translations = $this->translationRepository->findBy($criteria);
        usort($translations, static function (SitePageTranslation $left, SitePageTranslation $right): int {
            return strcmp((string) $left->getSitePage()?->getSlug(), (string) $right->getSitePage()?->getSlug());
        });

        return [
            'entity' => 'SitePageTranslation',
            'locale' => $locale,
            'exportedAt' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'translations' => array_map(fn (SitePageTranslation $translation): array => $this->toRow($translation), $translations),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function writeExport(array $payload, ?string $path = null): string
    {
        $locale = (string) ($payload['locale'] ?? '');
        $path ??= $this->defaultPath($locale);
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0775, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Cannot create export directory "%s".', $directory));
        }

        file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n");

        return $path;
    }

    /**
     * @return array{created:int, updated:int, unchanged:int, conflict:int, rows:int, dryRun:bool, conflicts:string[]}
     */
    public function import(string $locale, ?string $path = null, bool $dryRun = false, bool $overwrite = false): array
    {
        SitePageTranslation::assertSupportedLocale($locale);
        $path ??= $this->defaultPath($locale);
        $payload = json_decode((string) file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
        if (!is_array($payload) || ($payload['locale'] ?? null) !== $locale || !is_array($payload['translations'] ?? null)) {
            throw new \RuntimeException(sprintf('Invalid translation export "%s".', $path));
        }

        $counts = ['created' => 0, 'updated' => 0, 'unchanged' => 0, 'conflict' => 0, 'rows' => 0, 'dryRun' => $dryRun, 'conflicts' => []];
        foreach ($payload['translations'] as $row) {
            if (!is_array($row)) {
                throw new \RuntimeException('Invalid translation row.');
            }

            ++$counts['rows'];
            $result = $this->importRow($row, $locale, $dryRun, $overwrite);
            ++$counts[$result['status']];
            if ($result['message'] !== null) {
                $counts['conflicts'][] = $result['message'];
            }
        }

        if (!$dryRun && $counts['conflict'] === 0) {
            $this->entityManager->flush();
        }

        return $counts;
    }

    /**
     * @return array<string, mixed>
     */
    public function toRow(SitePageTranslation $translation): array
    {
        $page = $translation->getSitePage();
        if (!$page instanceof SitePage) {
            throw new \RuntimeException('Cannot export detached SitePageTranslation.');
        }

        return [
            'source' => [
                'entity' => 'SitePage',
                'slug' => $page->getSlug(),
                'externalId' => $page->getExternalId(),
            ],
            'locale' => $translation->getLocale(),
            'slug' => $translation->getSlug(),
            'title' => $translation->getTitle(),
            'seoTitle' => $translation->getSeoTitle(),
            'seoDescription' => $translation->getSeoDescription(),
            'ogTitle' => $translation->getOgTitle(),
            'ogDescription' => $translation->getOgDescription(),
            'imageAlt' => $translation->getImageAlt(),
            'heroBadge' => $translation->getHeroBadge(),
            'heroTitle' => $translation->getHeroTitle(),
            'heroIntro' => $translation->getHeroIntro(),
            'heroSideHtml' => $translation->getHeroSideHtml(),
            'bodyHtml' => $translation->getBodyHtml(),
            'structuredData' => $translation->getStructuredData(),
            'status' => $translation->getTranslationStatus(),
            'sourceContentHash' => $translation->getSourceContentHash(),
            'sourceUpdatedAt' => $this->formatDate($translation->getSourceUpdatedAt()),
            'translatedAt' => $this->formatDate($translation->getTranslatedAt()),
            'reviewedAt' => $this->formatDate($translation->getReviewedAt()),
            'publishedAt' => $this->formatDate($translation->getPublishedAt()),
            'unpublishedAt' => $this->formatDate($translation->getUnpublishedAt()),
            'isOutdated' => $this->isOutdated($page, $translation),
        ];
    }

    public function defaultPath(string $locale): string
    {
        return $this->kernel->getProjectDir().self::DEFAULT_DIR.'/site_pages.'.$locale.'.json';
    }

    /**
     * @param array<string, mixed> $row
     * @return array{status:'created'|'updated'|'unchanged'|'conflict', message:?string}
     */
    private function importRow(array $row, string $locale, bool $dryRun, bool $overwrite): array
    {
        $source = $row['source'] ?? null;
        $sourceSlug = is_array($source) ? (string) ($source['slug'] ?? '') : '';
        $page = $sourceSlug !== '' ? $this->sitePageRepository->findOneBy(['slug' => $sourceSlug]) : null;
        if (!$page instanceof SitePage) {
            return ['status' => 'conflict', 'message' => sprintf('CONFLICT source page missing: %s', $sourceSlug)];
        }

        if (($row['locale'] ?? null) !== $locale) {
            return ['status' => 'conflict', 'message' => sprintf('CONFLICT locale mismatch for %s', $sourceSlug)];
        }

        $slug = (string) ($row['slug'] ?? '');
        $collision = $this->translationRepository->findOneBy(['locale' => $locale, 'slug' => $slug]);
        if ($collision instanceof SitePageTranslation && $collision->getSitePage() !== $page) {
            return ['status' => 'conflict', 'message' => sprintf('CONFLICT slug collision: %s/%s', $locale, $slug)];
        }

        $translation = $this->translationRepository->findOneByPageAndLocale($page, $locale);
        if (!$translation instanceof SitePageTranslation) {
            if (!$dryRun) {
                $translation = (new SitePageTranslation())->setLocale($locale);
                $page->addTranslation($translation);
                $this->applyRow($translation, $row);
                $this->entityManager->persist($translation);
            }

            return ['status' => 'created', 'message' => null];
        }

        if ($this->rowsEqual($this->toComparableRow($translation), $this->toComparableRow($row))) {
            return ['status' => 'unchanged', 'message' => null];
        }

        if (!$overwrite) {
            return ['status' => 'conflict', 'message' => sprintf('CONFLICT existing translation differs: %s/%s', $locale, $sourceSlug)];
        }

        if (!$dryRun) {
            $this->applyRow($translation, $row);
        }

        return ['status' => 'updated', 'message' => null];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function applyRow(SitePageTranslation $translation, array $row): void
    {
        $translation
            ->setSlug((string) $row['slug'])
            ->setTitle((string) $row['title'])
            ->setSeoTitle($this->nullableString($row['seoTitle'] ?? null))
            ->setSeoDescription($this->nullableString($row['seoDescription'] ?? null))
            ->setOgTitle($this->nullableString($row['ogTitle'] ?? null))
            ->setOgDescription($this->nullableString($row['ogDescription'] ?? null))
            ->setImageAlt($this->nullableString($row['imageAlt'] ?? null))
            ->setHeroBadge($this->nullableString($row['heroBadge'] ?? null))
            ->setHeroTitle($this->nullableString($row['heroTitle'] ?? null))
            ->setHeroIntro($this->nullableString($row['heroIntro'] ?? null))
            ->setHeroSideHtml($this->nullableString($row['heroSideHtml'] ?? null))
            ->setBodyHtml($this->nullableString($row['bodyHtml'] ?? null))
            ->setStructuredData(is_array($row['structuredData'] ?? null) ? $row['structuredData'] : null)
            ->setTranslationStatus((string) $row['status'])
            ->setSourceContentHash($this->nullableString($row['sourceContentHash'] ?? null))
            ->setSourceUpdatedAt($this->parseDate($row['sourceUpdatedAt'] ?? null))
            ->setTranslatedAt($this->parseDate($row['translatedAt'] ?? null))
            ->setReviewedAt($this->parseDate($row['reviewedAt'] ?? null))
            ->setPublishedAt($this->parseDate($row['publishedAt'] ?? null))
            ->setUnpublishedAt($this->parseDate($row['unpublishedAt'] ?? null));
    }

    /**
     * @param array<string, mixed>|SitePageTranslation $value
     * @return array<string, mixed>
     */
    private function toComparableRow(array|SitePageTranslation $value): array
    {
        $row = $value instanceof SitePageTranslation ? $this->toRow($value) : $value;
        unset($row['isOutdated']);

        return $row;
    }

    /**
     * @param array<string, mixed> $left
     * @param array<string, mixed> $right
     */
    private function rowsEqual(array $left, array $right): bool
    {
        return json_encode($left, JSON_THROW_ON_ERROR) === json_encode($right, JSON_THROW_ON_ERROR);
    }

    private function isOutdated(SitePage $page, SitePageTranslation $translation): bool
    {
        $source = $page->getTranslation(SitePageTranslation::LOCALE_FR) ?? $this->translationRepository->findOneByPageAndLocale($page, SitePageTranslation::LOCALE_FR);
        if (!$source instanceof SitePageTranslation) {
            return false;
        }

        return $translation->isOutdated($this->sourceHasher->hashSitePageTranslation($source));
    }

    private function formatDate(?\DateTimeImmutable $date): ?string
    {
        return $date?->format(\DateTimeInterface::ATOM);
    }

    private function parseDate(mixed $value): ?\DateTimeImmutable
    {
        return is_string($value) && $value !== '' ? new \DateTimeImmutable($value) : null;
    }

    private function nullableString(mixed $value): ?string
    {
        return $value === null ? null : (string) $value;
    }
}
