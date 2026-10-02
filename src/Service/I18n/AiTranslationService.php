<?php

namespace App\Service\I18n;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageTranslationRepository;
use App\Service\TranslationSourceHasher;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class AiTranslationService
{
    private const SITE_PAGE_FIELDS = [
        'slug',
        'title',
        'seoTitle',
        'seoDescription',
        'ogTitle',
        'ogDescription',
        'imageAlt',
        'heroBadge',
        'heroTitle',
        'heroIntro',
        'heroSideHtml',
        'bodyHtml',
        'structuredData',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageTranslationRepository $sitePageTranslationRepository,
        private readonly TranslationSourceHasher $sourceHasher,
        private readonly AiTranslationProviderInterface $provider,
        private readonly KernelInterface $kernel,
    ) {
    }

    /**
     * @return array{entity: string, id: int|null, slug: string|null, locale: string, fields: list<string>, currentStatus: string|null, outdated: bool, action: string}
     */
    public function planSitePage(SitePage $page, string $targetLocale, bool $overwrite = false, bool $onlyMissing = false, bool $onlyOutdated = false): array
    {
        $source = $this->sourceTranslation($page);
        $target = $page->getTranslation($targetLocale) ?? $this->sitePageTranslationRepository->findOneByPageAndLocale($page, $targetLocale);
        $sourceHash = $this->sourceHasher->hashSitePageTranslation($source);
        $outdated = $target instanceof SitePageTranslation && $target->isOutdated($sourceHash);
        $action = 'generate';

        if ($target instanceof SitePageTranslation && !$overwrite) {
            $action = $outdated && $onlyOutdated ? 'regenerate_outdated' : 'skip_exists';
        }

        if ($onlyMissing && $target instanceof SitePageTranslation) {
            $action = 'skip_exists';
        }

        if ($onlyOutdated && (!$target instanceof SitePageTranslation || !$outdated)) {
            $action = 'skip_not_outdated';
        }

        if ($target instanceof SitePageTranslation && $overwrite) {
            $action = $outdated ? 'regenerate_outdated' : 'overwrite';
        }

        return [
            'entity' => 'SitePage',
            'id' => $page->getId(),
            'slug' => $source->getSlug(),
            'locale' => $targetLocale,
            'fields' => self::SITE_PAGE_FIELDS,
            'currentStatus' => $target?->getTranslationStatus(),
            'outdated' => $outdated,
            'action' => $action,
        ];
    }

    public function translateSitePage(SitePage $page, string $targetLocale, bool $overwrite = false, bool $onlyMissing = false, bool $onlyOutdated = false): SitePageTranslation
    {
        SitePageTranslation::assertSupportedLocale($targetLocale);
        if ($targetLocale === SitePageTranslation::LOCALE_FR) {
            throw new \InvalidArgumentException('AI translation target locale must not be fr.');
        }

        $plan = $this->planSitePage($page, $targetLocale, $overwrite, $onlyMissing, $onlyOutdated);
        if (str_starts_with($plan['action'], 'skip')) {
            throw new \RuntimeException(sprintf('Skipped SitePage #%s: %s.', $page->getId() ?? 'new', $plan['action']));
        }

        $source = $this->sourceTranslation($page);
        $sourcePayload = $this->payloadFromTranslation($source);
        $sourceHash = $this->sourceHasher->hashSitePageTranslation($source);
        $result = $this->provider->translate($sourcePayload, $targetLocale, $this->glossary());
        $payload = $this->normalizePayload($result->payload);
        $this->validatePayload($sourcePayload, $payload);
        $this->assertSlugAvailable($page, $targetLocale, $payload['slug']);

        $target = $page->getTranslation($targetLocale) ?? $this->sitePageTranslationRepository->findOneByPageAndLocale($page, $targetLocale);
        if (!$target instanceof SitePageTranslation) {
            $target = (new SitePageTranslation())->setLocale($targetLocale);
            $page->addTranslation($target);
            $this->entityManager->persist($target);
        }

        $this->applyPayload($target, $payload);
        $target
            ->setTranslationStatus(SitePageTranslation::STATUS_AI_TRANSLATED)
            ->setSourceContentHash($sourceHash)
            ->setSourceUpdatedAt(new \DateTimeImmutable())
            ->setTranslatedAt(new \DateTimeImmutable())
            ->setReviewedAt(null)
            ->setReviewedBy(null)
            ->setPublishedAt(null)
            ->setUnpublishedAt(null);

        return $target;
    }

    private function sourceTranslation(SitePage $page): SitePageTranslation
    {
        $source = $page->getTranslation(SitePageTranslation::LOCALE_FR) ?? $this->sitePageTranslationRepository->findOneByPageAndLocale($page, SitePageTranslation::LOCALE_FR);
        if (!$source instanceof SitePageTranslation) {
            throw new \LogicException(sprintf('Missing FR source translation for SitePage #%s.', $page->getId() ?? 'new'));
        }

        return $source;
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromTranslation(SitePageTranslation $translation): array
    {
        return [
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
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function applyPayload(SitePageTranslation $translation, array $payload): void
    {
        $translation
            ->setSlug($payload['slug'])
            ->setTitle($payload['title'])
            ->setSeoTitle($payload['seoTitle'])
            ->setSeoDescription($payload['seoDescription'])
            ->setOgTitle($payload['ogTitle'])
            ->setOgDescription($payload['ogDescription'])
            ->setImageAlt($payload['imageAlt'])
            ->setHeroBadge($payload['heroBadge'])
            ->setHeroTitle($payload['heroTitle'])
            ->setHeroIntro($payload['heroIntro'])
            ->setHeroSideHtml($payload['heroSideHtml'])
            ->setBodyHtml($payload['bodyHtml'])
            ->setStructuredData($payload['structuredData']);
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $payload): array
    {
        $normalized = [];
        foreach (self::SITE_PAGE_FIELDS as $field) {
            $normalized[$field] = $payload[$field] ?? null;
        }
        $normalized['slug'] = $this->normalizeSlug((string) $normalized['slug']);
        $normalized['title'] = trim((string) $normalized['title']);

        return $normalized;
    }

    /**
     * @param array<string, mixed> $source
     * @param array<string, mixed> $target
     */
    private function validatePayload(array $source, array $target): void
    {
        foreach (self::SITE_PAGE_FIELDS as $field) {
            if (!array_key_exists($field, $target)) {
                throw new AiTranslationValidationException(sprintf('Missing translated field "%s".', $field));
            }
        }

        if ($target['slug'] === '' || preg_match('/^[a-z0-9][a-z0-9\-]*$/', $target['slug']) !== 1) {
            throw new AiTranslationValidationException('Translated slug is invalid.');
        }

        if ($target['title'] === '') {
            throw new AiTranslationValidationException('Translated title is required.');
        }

        $this->assertSameJsonShape($source['structuredData'], $target['structuredData'], 'structuredData');
        foreach (['heroIntro', 'heroSideHtml', 'bodyHtml'] as $field) {
            $this->assertPlaceholdersPreserved((string) ($source[$field] ?? ''), (string) ($target[$field] ?? ''), $field);
        }
    }

    private function assertSlugAvailable(SitePage $page, string $locale, string $slug): void
    {
        $existing = $this->sitePageTranslationRepository->findOneBy(['locale' => $locale, 'slug' => $slug]);
        if ($existing instanceof SitePageTranslation && $existing->getSitePage() !== $page) {
            throw new AiTranslationConflictException(sprintf('CONFLICT: slug "%s" already exists for locale "%s".', $slug, $locale));
        }
    }

    private function normalizeSlug(string $slug): string
    {
        $slug = trim(mb_strtolower($slug));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }

    private function assertSameJsonShape(mixed $source, mixed $target, string $path): void
    {
        if ($source === null) {
            return;
        }
        if (is_array($source) !== is_array($target)) {
            throw new AiTranslationValidationException(sprintf('Translated JSON shape mismatch at "%s".', $path));
        }
        if (!is_array($source) || !is_array($target)) {
            return;
        }

        if ($this->isList($source)) {
            if (!$this->isList($target) || count($source) !== count($target)) {
                throw new AiTranslationValidationException(sprintf('Translated JSON list shape mismatch at "%s".', $path));
            }
            foreach ($source as $index => $value) {
                $this->assertSameJsonShape($value, $target[$index] ?? null, $path.'.'.$index);
            }
            return;
        }

        $sourceKeys = array_keys($source);
        $targetKeys = array_keys($target);
        sort($sourceKeys);
        sort($targetKeys);
        if ($sourceKeys !== $targetKeys) {
            throw new AiTranslationValidationException(sprintf('Translated JSON keys mismatch at "%s".', $path));
        }
        foreach ($source as $key => $value) {
            $this->assertSameJsonShape($value, $target[$key] ?? null, $path.'.'.$key);
        }
    }

    private function assertPlaceholdersPreserved(string $source, string $target, string $field): void
    {
        $pattern = '/(\\{\\{\\s*[^}]+\\s*\\}\\}|%[a-zA-Z0-9_]+%|\\{[a-zA-Z0-9_]+\\})/';
        preg_match_all($pattern, $source, $sourceMatches);
        preg_match_all($pattern, $target, $targetMatches);
        $sourceTokens = array_values(array_unique($sourceMatches[0]));
        $targetTokens = array_values(array_unique($targetMatches[0]));
        sort($sourceTokens);
        sort($targetTokens);
        if ($sourceTokens !== $targetTokens) {
            throw new AiTranslationValidationException(sprintf('Placeholders mismatch in "%s".', $field));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function glossary(): array
    {
        $path = $this->kernel->getProjectDir().'/data/i18n/glossary.json';
        $raw = file_get_contents($path);
        if ($raw === false) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function isList(array $value): bool
    {
        return function_exists('array_is_list') ? array_is_list($value) : array_keys($value) === range(0, count($value) - 1);
    }
}
