<?php

namespace App\Service;

use App\Dto\GrowthNewsInput;
use App\Entity\SitePage;
use App\Entity\SitePageRevision;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Repository\SitePageRevisionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GrowthPublishingService
{
    private const STATUS_DRAFT = 'draft';
    private const STATUS_PUBLISHED = 'published';
    private const STATUS_UNPUBLISHED = 'unpublished';
    private const RESOURCE_PREFIX = 'ressource-';
    private const BLOCKED_PUBLIC_SLUG_PREFIXES = [
        'pilot-',
        'test-',
        'demo-',
        'e2e-',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly SitePageRevisionRepository $revisionRepository,
        private readonly GrowthContentSanitizer $sanitizer,
        private readonly LoggerInterface $logger,
        private readonly GrowthMediaStore $mediaStore,
    ) {
    }

    public function createOrUpdateDraft(GrowthNewsInput $input): array
    {
        $page = $this->sitePageRepository->findOneByExternalId($input->externalId);
        if ($page === null && $input->contentGroupId !== '') {
            $page = $this->sitePageRepository->findOneBy(['sourceCampaignId' => $input->contentGroupId]);
        }
        $created = false;
        if ($page === null) {
            $page = new SitePage();
            $page->setExternalId($input->contentGroupId !== '' ? $input->contentGroupId : $input->externalId);
            $page->setPublicationStatus(self::STATUS_DRAFT);
            $this->entityManager->persist($page);
            $created = true;
        }
        $page->setSourceCampaignId($this->sanitizer->sanitizeText($input->contentGroupId ?: $input->sourceCampaignId, 190));

        $this->assertSlugAvailable($input->slug, $page, $input->locale);

        if (($created || $input->locale === SitePageTranslation::LOCALE_FR) && $page->getPublicationStatus() !== self::STATUS_PUBLISHED) {
            $this->applyRevisionToProjection($page, $input, self::STATUS_DRAFT);
        }
        $this->applyTranslationDraft($page, $input);

        $revision = $this->createRevision($page, $input, 'draft');
        $this->entityManager->flush();

        $this->logger->info('growth_draft_saved', [
            'external_id' => $input->externalId,
            'revision' => $revision->getRevisionNumber(),
            'created' => $created,
        ]);

        return $this->buildStatusPayload($page, $revision, $created ? 201 : 200, $input->locale);
    }

    public function getStatus(string $externalId, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $locale = $this->normalizeLocale($locale);
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraftForLocale($page, $locale);

        return $this->buildStatusPayload($page, $draft, 200, $locale);
    }

    public function publish(string $externalId, string $locale = SitePageTranslation::LOCALE_FR, ?int $expectedRevision = null): array
    {
        $locale = $this->normalizeLocale($locale);
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraftForLocale($page, $locale);
        if ($draft === null) {
            throw new ConflictHttpException('No draft available for publication.');
        }
        if ($expectedRevision !== null && $draft->getRevisionNumber() !== $expectedRevision) {
            throw new ConflictHttpException('Destination draft revision changed; publication refused.');
        }

        $this->mediaStore->publishHtmlMedia($draft->getContentHtml());

        $publishedRevision = $this->cloneRevision($page, $draft, 'published');
        $draft->setRevisionState('consumed');
        $this->applyRevisionToTranslation($page, $locale, $publishedRevision);
        if ($locale === SitePageTranslation::LOCALE_FR) {
            $this->applyRevisionSnapshot($page, $publishedRevision, self::STATUS_PUBLISHED);
            $page->setPublishedAt(new \DateTimeImmutable());
            $page->setUnpublishedAt(null);
        }
        $this->entityManager->flush();

        $this->logger->info('growth_draft_published', [
            'external_id' => $externalId,
            'locale' => $locale,
            'revision' => $publishedRevision->getRevisionNumber(),
        ]);

        return $this->buildStatusPayload($page, null, 200, $locale);
    }

    public function unpublish(string $externalId, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $locale = $this->normalizeLocale($locale);
        $page = $this->findPage($externalId);
        $translation = $page->getTranslation($locale);
        if ($translation instanceof SitePageTranslation) {
            if (!$this->isTranslationPublic($translation)) {
                throw new ConflictHttpException('Only a published article can be unpublished.');
            }
            $translation->setUnpublishedAt(new \DateTimeImmutable());
            if ($locale === SitePageTranslation::LOCALE_FR) {
                $page->setPublicationStatus(self::STATUS_UNPUBLISHED);
                $page->setUnpublishedAt(new \DateTimeImmutable());
            }
        } elseif ($locale === SitePageTranslation::LOCALE_FR && $page->getPublicationStatus() === self::STATUS_PUBLISHED) {
            $page->setPublicationStatus(self::STATUS_UNPUBLISHED);
            $page->setUnpublishedAt(new \DateTimeImmutable());
        } else {
            throw new ConflictHttpException('Only a published article can be unpublished.');
        }
        $this->entityManager->flush();

        $this->logger->info('growth_article_unpublished', ['external_id' => $externalId, 'locale' => $locale]);

        return $this->buildStatusPayload($page, $this->findActiveDraftForLocale($page, $locale), 200, $locale);
    }

    public function restorePreviousPublishedVersion(string $externalId, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $locale = $this->normalizeLocale($locale);
        $page = $this->findPage($externalId);
        $publishedRevisions = $this->findPublishedRevisionsForLocale($page, $locale, 2);
        if (count($publishedRevisions) < 2) {
            throw new ConflictHttpException('No previous published version is available.');
        }

        $previousRevision = $publishedRevisions[1];
        $restoredRevision = $this->cloneRevision($page, $previousRevision, 'published');
        $this->applyRevisionToTranslation($page, $locale, $restoredRevision);
        if ($locale === SitePageTranslation::LOCALE_FR) {
            $this->applyRevisionSnapshot($page, $restoredRevision, self::STATUS_PUBLISHED);
            $page->setPublishedAt(new \DateTimeImmutable());
            $page->setUnpublishedAt(null);
        }
        $this->entityManager->flush();

        $this->logger->info('growth_article_restored', [
            'external_id' => $externalId,
            'locale' => $locale,
            'restored_from_revision' => $previousRevision->getRevisionNumber(),
            'new_revision' => $restoredRevision->getRevisionNumber(),
        ]);

        return $this->buildStatusPayload($page, $this->findActiveDraftForLocale($page, $locale), 200, $locale);
    }

    public function buildPreviewPage(string $externalId, string $locale = SitePageTranslation::LOCALE_FR): SitePageRevision
    {
        $locale = $this->normalizeLocale($locale);
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraftForLocale($page, $locale) ?? ($this->findPublishedRevisionsForLocale($page, $locale, 1)[0] ?? null);
        if ($draft === null) {
            throw new ConflictHttpException('No draft available for preview.');
        }

        return $draft;
    }

    public function buildPreviewView(SitePageRevision $revision): SitePage
    {
        $page = new SitePage();
        $page->setExternalId($revision->getSitePage()?->getExternalId());
        $this->applyRevisionSnapshot($page, $revision, self::STATUS_DRAFT);
        $page->setHeroSideHtml($this->mediaStore->privatePreviewHtml($revision->getContentHtml()));
        if (str_contains($revision->getContentHtml(), '/uploads/growth/')) {
            $page->setHeroImage(null);
        }

        return $page;
    }

    private function findPage(string $externalId): SitePage
    {
        $page = $this->sitePageRepository->findOneByExternalId($externalId);
        if ($page === null) {
            throw new NotFoundHttpException('Unknown external_id.');
        }

        return $page;
    }

    private function assertSlugAvailable(string $publicSlug, SitePage $currentPage, string $locale = SitePageTranslation::LOCALE_FR): void
    {
        foreach (self::BLOCKED_PUBLIC_SLUG_PREFIXES as $prefix) {
            if (str_starts_with($publicSlug, $prefix)) {
                throw new ConflictHttpException('This slug is blocked and cannot be published.');
            }
        }

        $translationSlug = $locale === SitePageTranslation::LOCALE_FR ? self::RESOURCE_PREFIX.$publicSlug : $publicSlug;
        $existingTranslation = $this->entityManager->getRepository(SitePageTranslation::class)->findOneBy(['locale' => $locale, 'slug' => $translationSlug]);
        if ($existingTranslation instanceof SitePageTranslation && $existingTranslation->getSitePage()?->getId() !== $currentPage->getId()) {
            throw new ConflictHttpException('This localized slug is already used by another article.');
        }

        if ($locale !== SitePageTranslation::LOCALE_FR) {
            return;
        }

        $existing = $this->sitePageRepository->findOneBy(['slug' => self::RESOURCE_PREFIX.$publicSlug]);
        if ($existing !== null && $existing->getId() !== $currentPage->getId()) {
            throw new ConflictHttpException('This slug is already used by another article.');
        }
    }

    private function createRevision(SitePage $page, GrowthNewsInput $input, string $revisionState): SitePageRevision
    {
        $revision = (new SitePageRevision())
            ->setSitePage($page)
            ->setRevisionNumber($this->revisionRepository->nextRevisionNumber($page))
            ->setRevisionState($revisionState)
            ->setTitle($this->sanitizer->sanitizeText($input->title, 255))
            ->setSlug($input->slug)
            ->setExcerpt($this->sanitizer->sanitizeHtml($input->excerpt))
            ->setContentHtml($this->sanitizer->sanitizeHtml($input->contentHtml))
            ->setMetaTitle($this->sanitizer->sanitizeText($input->metaTitle, 255))
            ->setMetaDescription($this->sanitizer->sanitizeText($input->metaDescription, 2000))
            ->setCanonicalUrl($this->normalizeUrl($input->canonicalUrl))
            ->setFeaturedImage($this->normalizeMediaPath($input->featuredImage))
            ->setCategories($this->sanitizeTerms($input->categories))
            ->setTags($this->sanitizeTerms($input->tags))
            ->setPublicationDate($input->publicationDate ?? new \DateTimeImmutable())
            ->setStatus($input->status)
            ->setAuthorDisplayName($this->sanitizer->sanitizeText($input->authorDisplayName, 255))
            ->setSourceCampaignId($this->sanitizer->sanitizeText($input->sourceCampaignId, 190))
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($revision);

        return $revision;
    }

    private function cloneRevision(SitePage $page, SitePageRevision $source, string $revisionState): SitePageRevision
    {
        $revision = (new SitePageRevision())
            ->setSitePage($page)
            ->setRevisionNumber($this->revisionRepository->nextRevisionNumber($page))
            ->setRevisionState($revisionState)
            ->setTitle($source->getTitle())
            ->setSlug($source->getSlug())
            ->setExcerpt($source->getExcerpt())
            ->setContentHtml($source->getContentHtml())
            ->setMetaTitle($source->getMetaTitle())
            ->setMetaDescription($source->getMetaDescription())
            ->setCanonicalUrl($source->getCanonicalUrl())
            ->setFeaturedImage($source->getFeaturedImage())
            ->setCategories($source->getCategories())
            ->setTags($source->getTags())
            ->setPublicationDate($source->getPublicationDate())
            ->setStatus($source->getStatus())
            ->setAuthorDisplayName($source->getAuthorDisplayName())
            ->setSourceCampaignId($source->getSourceCampaignId())
            ->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($revision);

        return $revision;
    }

    private function applyRevisionToProjection(SitePage $page, GrowthNewsInput $input, string $status): void
    {
        $page->setSlug(self::RESOURCE_PREFIX.$input->slug);
        $page->setTitle($this->sanitizer->sanitizeText($input->metaTitle, 255));
        $page->setMetaDescription($this->sanitizer->sanitizeText($input->metaDescription, 2000));
        $page->setHeroTitle($this->sanitizer->sanitizeText($input->title, 255));
        $page->setHeroIntro($this->sanitizer->sanitizeHtml($input->excerpt));
        $page->setHeroSideHtml($this->sanitizer->sanitizeHtml($input->contentHtml));
        if ($this->growthMayUpdateHeroImage($page)) {
            $page->setHeroImage($this->normalizeMediaPath($input->featuredImage));
        }
        $page->setCanonicalUrl($this->normalizeUrl($input->canonicalUrl));
        $page->setCategories($this->sanitizeTerms($input->categories));
        $page->setTags($this->sanitizeTerms($input->tags));
        $page->setPublicationDate($input->publicationDate);
        $page->setAuthorDisplayName($this->sanitizer->sanitizeText($input->authorDisplayName, 255));
        $page->setSourceCampaignId($this->sanitizer->sanitizeText($input->sourceCampaignId, 190));
        $page->setPublicationStatus($status);
    }

    private function applyTranslationDraft(SitePage $page, GrowthNewsInput $input): void
    {
        $translation = $page->getTranslation($input->locale);
        if ($translation instanceof SitePageTranslation && $this->isTranslationPublic($translation)) {
            return;
        }
        if (!$translation instanceof SitePageTranslation) {
            $translation = (new SitePageTranslation())->setLocale($input->locale);
            $page->addTranslation($translation);
        }

        $translation
            ->setSlug($input->locale === SitePageTranslation::LOCALE_FR ? self::RESOURCE_PREFIX.$input->slug : $input->slug)
            ->setTitle($this->sanitizer->sanitizeText($input->metaTitle, 255))
            ->setHeroTitle($this->sanitizer->sanitizeText($input->title, 255))
            ->setHeroIntro($this->sanitizer->sanitizeHtml($input->excerpt))
            ->setHeroSideHtml($this->sanitizer->sanitizeHtml($input->contentHtml))
            ->setBodyHtml($this->sanitizer->sanitizeHtml($input->contentHtml))
            ->setSeoTitle($this->sanitizer->sanitizeText($input->metaTitle, 255))
            ->setSeoDescription($this->sanitizer->sanitizeText($input->metaDescription, 2000))
            ->setOgTitle($this->sanitizer->sanitizeText($input->metaTitle, 255))
            ->setOgDescription($this->sanitizer->sanitizeText($input->metaDescription, 2000))
            ->setImageAlt($this->sanitizer->sanitizeText($input->title, 255))
            ->setTranslationStatus(SitePageTranslation::STATUS_DRAFT)
            ->setUnpublishedAt(null);
    }

    private function applyRevisionSnapshot(SitePage $page, SitePageRevision $revision, string $status): void
    {
        $page->setSlug(self::RESOURCE_PREFIX.$revision->getSlug());
        $page->setTitle($revision->getMetaTitle());
        $page->setMetaDescription($revision->getMetaDescription());
        $page->setHeroTitle($revision->getTitle());
        $page->setHeroIntro($revision->getExcerpt());
        $page->setHeroSideHtml($revision->getContentHtml());
        if ($this->growthMayUpdateHeroImage($page)) {
            $page->setHeroImage($revision->getFeaturedImage());
        }
        $page->setCanonicalUrl($revision->getCanonicalUrl());
        $page->setCategories($revision->getCategories());
        $page->setTags($revision->getTags());
        $page->setPublicationDate($revision->getPublicationDate());
        $page->setAuthorDisplayName($revision->getAuthorDisplayName());
        $page->setSourceCampaignId($revision->getSourceCampaignId());
        $page->setPublicationStatus($status);
    }

    private function applyRevisionToTranslation(SitePage $page, string $locale, SitePageRevision $revision): void
    {
        $translation = $page->getTranslation($locale);
        if (!$translation instanceof SitePageTranslation) {
            $translation = (new SitePageTranslation())->setLocale($locale);
            $page->addTranslation($translation);
        }

        $translation->setStructuredData(array_replace($translation->getStructuredData() ?? [], [
            'growth' => ['canonical_url' => $revision->getCanonicalUrl(), 'featured_image' => $revision->getFeaturedImage()],
        ]));
        $translation
            ->setSlug($locale === SitePageTranslation::LOCALE_FR ? self::RESOURCE_PREFIX.$revision->getSlug() : $revision->getSlug())
            ->setTitle($revision->getMetaTitle())
            ->setHeroTitle($revision->getTitle())
            ->setHeroIntro($revision->getExcerpt())
            ->setHeroSideHtml($revision->getContentHtml())
            ->setBodyHtml($revision->getContentHtml())
            ->setSeoTitle($revision->getMetaTitle())
            ->setSeoDescription($revision->getMetaDescription())
            ->setOgTitle($revision->getMetaTitle())
            ->setOgDescription($revision->getMetaDescription())
            ->setImageAlt($revision->getTitle())
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable())
            ->setUnpublishedAt(null);
    }

    private function growthMayUpdateHeroImage(SitePage $page): bool
    {
        return $page->getHeroMediaMode() === SitePage::HERO_MEDIA_MODE_INHERIT;
    }

    private function buildStatusPayload(SitePage $page, ?SitePageRevision $draftRevision, int $httpCode, string $locale = SitePageTranslation::LOCALE_FR): array
    {
        $locale = $this->normalizeLocale($locale);
        $publishedRevisions = $this->findPublishedRevisionsForLocale($page, $locale, 1);
        $publishedRevision = $publishedRevisions[0] ?? null;
        $hasPendingDraft = $draftRevision !== null
            && ($publishedRevision === null || $draftRevision->getRevisionNumber() > $publishedRevision->getRevisionNumber());
        $translation = $page->getTranslation($locale);
        $publicSlug = $draftRevision instanceof SitePageRevision ? $draftRevision->getSlug() : $this->publicSlugForLocale($page, $locale);

        return [
            'http_code' => $httpCode,
            'external_id' => $page->getExternalId(),
            'locale' => $locale,
            'locales' => array_map(fn (SitePageTranslation $translation): array => [
                'locale' => $translation->getLocale(),
                'slug' => $translation->getSlug(),
                'status' => $this->translationPublicationStatus($translation),
                'published_at' => $translation->getPublishedAt()?->format(DATE_ATOM),
                'unpublished_at' => $translation->getUnpublishedAt()?->format(DATE_ATOM),
            ], $page->getTranslations()->toArray()),
            'public_slug' => $publicSlug,
            'publication_status' => $translation instanceof SitePageTranslation ? $this->translationPublicationStatus($translation) : $page->getPublicationStatus(),
            'has_pending_draft' => $hasPendingDraft,
            'draft_revision_number' => $hasPendingDraft ? $draftRevision?->getRevisionNumber() : null,
            'published_revision_number' => $publishedRevision?->getRevisionNumber(),
            'published_at' => $translation instanceof SitePageTranslation ? $translation->getPublishedAt()?->format(DATE_ATOM) : $page->getPublishedAt()?->format(DATE_ATOM),
            'unpublished_at' => $translation instanceof SitePageTranslation ? $translation->getUnpublishedAt()?->format(DATE_ATOM) : $page->getUnpublishedAt()?->format(DATE_ATOM),
        ];
    }

    private function findActiveDraft(SitePage $page): ?SitePageRevision
    {
        $draft = $this->revisionRepository->findLatestDraft($page);
        if ($draft === null) {
            return null;
        }

        $publishedRevisions = $this->revisionRepository->findPublishedRevisions($page, 1);
        $publishedRevision = $publishedRevisions[0] ?? null;

        if ($publishedRevision !== null && $draft->getRevisionNumber() <= $publishedRevision->getRevisionNumber()) {
            return null;
        }

        return $draft;
    }

    private function findActiveDraftForLocale(SitePage $page, string $locale): ?SitePageRevision
    {
        $slug = $this->publicSlugForLocale($page, $locale);
        $draft = null;
        if ($slug !== '') {
            $draft = $this->revisionRepository->findLatestDraftForSlugAndCanonicalFragment($page, $slug, $this->canonicalFragmentForLocale($locale))
                ?? $this->revisionRepository->findLatestDraftForSlug($page, $slug);
        }
        if (!$draft instanceof SitePageRevision && $locale === SitePageTranslation::LOCALE_FR) {
            return $this->findActiveDraft($page);
        }

        if (!$draft instanceof SitePageRevision) {
            return null;
        }

        $publishedRevision = $this->findPublishedRevisionsForLocale($page, $locale, 1)[0] ?? null;
        if ($publishedRevision !== null && $draft->getRevisionNumber() <= $publishedRevision->getRevisionNumber()) {
            return null;
        }

        return $draft;
    }

    private function findPublishedRevisionsForLocale(SitePage $page, string $locale, int $limit = 10): array
    {
        $slug = $this->publicSlugForLocale($page, $locale);
        if ($slug !== '') {
            $localized = $this->revisionRepository->findPublishedRevisionsForSlugAndCanonicalFragment($page, $slug, $this->canonicalFragmentForLocale($locale), $limit);
            return $localized !== [] ? $localized : $this->revisionRepository->findPublishedRevisionsForSlug($page, $slug, $limit);
        }

        return $locale === SitePageTranslation::LOCALE_FR ? $this->revisionRepository->findPublishedRevisions($page, $limit) : [];
    }

    private function publicSlugForLocale(SitePage $page, string $locale): string
    {
        $translation = $page->getTranslation($locale);
        if ($translation instanceof SitePageTranslation && $translation->getSlug() !== '') {
            return $locale === SitePageTranslation::LOCALE_FR ? $this->extractPublicSlug($translation->getSlug()) : $translation->getSlug();
        }

        return $locale === SitePageTranslation::LOCALE_FR ? $this->extractPublicSlug((string) $page->getSlug()) : '';
    }

    private function normalizeLocale(string $locale): string
    {
        return match ($locale) {
            'en-US', 'en_GB', 'en' => SitePageTranslation::LOCALE_EN,
            'es-ES', 'es_MX', 'es' => SitePageTranslation::LOCALE_ES,
            'fr-FR', 'fr_FR', 'fr' => SitePageTranslation::LOCALE_FR,
            default => throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Unsupported Growth locale.'),
        };
    }

    private function canonicalFragmentForLocale(string $locale): string
    {
        return match ($locale) {
            SitePageTranslation::LOCALE_EN => '/en/resources/',
            SitePageTranslation::LOCALE_ES => '/es/recursos/',
            default => '/ressources/',
        };
    }

    private function translationPublicationStatus(SitePageTranslation $translation): string
    {
        if ($translation->getUnpublishedAt() instanceof \DateTimeInterface) {
            return self::STATUS_UNPUBLISHED;
        }

        if ($this->isTranslationPublic($translation)) {
            return self::STATUS_PUBLISHED;
        }

        return self::STATUS_DRAFT;
    }

    private function isTranslationPublic(SitePageTranslation $translation): bool
    {
        return $translation->getTranslationStatus() === SitePageTranslation::STATUS_PUBLISHED
            && $translation->getPublishedAt() instanceof \DateTimeInterface
            && !($translation->getUnpublishedAt() instanceof \DateTimeInterface);
    }

    private function extractPublicSlug(string $storedSlug): string
    {
        return str_starts_with($storedSlug, self::RESOURCE_PREFIX)
            ? substr($storedSlug, strlen(self::RESOURCE_PREFIX))
            : $storedSlug;
    }

    private function normalizeUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/')) {
            return $url;
        }

        return preg_match('#^https?://#i', $url) === 1 ? $url : null;
    }

    private function normalizeMediaPath(?string $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (str_starts_with($value, '/')) {
            return $value;
        }

        return preg_match('#^https?://#i', $value) === 1 ? $value : null;
    }

    private function sanitizeTerms(array $terms): array
    {
        $normalized = [];
        foreach ($terms as $term) {
            $term = $this->sanitizer->sanitizeText($term, 80);
            if ($term !== '') {
                $normalized[] = $term;
            }
        }

        return array_values(array_unique($normalized));
    }
    public function recordAdminEdit(SitePage $page): void
    {
        $this->assertAdminEditAllowed($page);
        $draft = $this->findActiveDraftForLocale($page, SitePageTranslation::LOCALE_FR);
        if ($draft !== null) { $draft->setRevisionState('consumed'); }
        $input = new GrowthNewsInput();
        $input->externalId = (string) $page->getExternalId();
        $input->slug = preg_replace('/^ressource-/', '', (string) $page->getSlug());
        $input->title = (string) $page->getHeroTitle();
        $input->excerpt = (string) $page->getHeroIntro();
        $input->contentHtml = (string) $page->getHeroSideHtml();
        $input->metaTitle = (string) $page->getTitle();
        $input->metaDescription = (string) $page->getMetaDescription();
        $input->canonicalUrl = $page->getCanonicalUrl();
        $input->featuredImage = $page->getHeroImage();
        $input->categories = $page->getCategories() ?? [];
        $input->tags = $page->getTags() ?? [];
        $input->publicationDate = $page->getPublicationDate();
        $input->authorDisplayName = (string) $page->getAuthorDisplayName();
        $input->sourceCampaignId = (string) $page->getSourceCampaignId();
        $narrative = $page->getTranslation('fr')?->getStructuredData()['landingNarrative'] ?? [];
        $input->title = $narrative['heroTitle'] ?? $input->title;
        $input->excerpt = $narrative['heroIntro'] ?? $input->excerpt;
        $input->metaTitle = $narrative['metaTitle'] ?? $input->metaTitle;
        $input->metaDescription = $narrative['metaDescription'] ?? $input->metaDescription;
        $state = $page->getPublicationStatus() === 'published' ? 'published' : 'draft';
        $this->createRevision($page, $input, $state);
    }
    public function assertAdminEditAllowed(SitePage $page): void
    {
        $this->assertSlugAvailable(preg_replace('/^ressource-/', '', (string) $page->getSlug()), $page, SitePageTranslation::LOCALE_FR);
    }
}
