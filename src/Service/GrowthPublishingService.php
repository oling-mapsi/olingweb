<?php

namespace App\Service;

use App\Dto\GrowthNewsInput;
use App\Entity\SitePage;
use App\Entity\SitePageRevision;
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
        private readonly LoggerInterface $logger
    ) {
    }

    public function createOrUpdateDraft(GrowthNewsInput $input): array
    {
        $page = $this->sitePageRepository->findOneByExternalId($input->externalId);
        $created = false;
        if ($page === null) {
            $page = new SitePage();
            $page->setExternalId($input->externalId);
            $page->setPublicationStatus(self::STATUS_DRAFT);
            $this->entityManager->persist($page);
            $created = true;
        }

        $this->assertSlugAvailable($input->slug, $page);

        if ($page->getPublicationStatus() !== self::STATUS_PUBLISHED) {
            $this->applyRevisionToProjection($page, $input, self::STATUS_DRAFT);
        }

        $revision = $this->createRevision($page, $input, 'draft');
        $this->entityManager->flush();

        $this->logger->info('growth_draft_saved', [
            'external_id' => $input->externalId,
            'revision' => $revision->getRevisionNumber(),
            'created' => $created,
        ]);

        return $this->buildStatusPayload($page, $revision, $created ? 201 : 200);
    }

    public function getStatus(string $externalId): array
    {
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraft($page);

        return $this->buildStatusPayload($page, $draft, 200);
    }

    public function publish(string $externalId): array
    {
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraft($page);
        if ($draft === null) {
            throw new ConflictHttpException('No draft available for publication.');
        }

        $publishedRevision = $this->cloneRevision($page, $draft, 'published');
        $draft->setRevisionState('consumed');
        $this->applyRevisionSnapshot($page, $publishedRevision, self::STATUS_PUBLISHED);
        $page->setPublishedAt(new \DateTimeImmutable());
        $page->setUnpublishedAt(null);
        $this->entityManager->flush();

        $this->logger->info('growth_draft_published', [
            'external_id' => $externalId,
            'revision' => $publishedRevision->getRevisionNumber(),
        ]);

        return $this->buildStatusPayload($page, null, 200);
    }

    public function unpublish(string $externalId): array
    {
        $page = $this->findPage($externalId);
        if ($page->getPublicationStatus() !== self::STATUS_PUBLISHED) {
            throw new ConflictHttpException('Only a published article can be unpublished.');
        }

        $page->setPublicationStatus(self::STATUS_UNPUBLISHED);
        $page->setUnpublishedAt(new \DateTimeImmutable());
        $this->entityManager->flush();

        $this->logger->info('growth_article_unpublished', ['external_id' => $externalId]);

        return $this->buildStatusPayload($page, $this->findActiveDraft($page), 200);
    }

    public function restorePreviousPublishedVersion(string $externalId): array
    {
        $page = $this->findPage($externalId);
        $publishedRevisions = $this->revisionRepository->findPublishedRevisions($page, 2);
        if (count($publishedRevisions) < 2) {
            throw new ConflictHttpException('No previous published version is available.');
        }

        $previousRevision = $publishedRevisions[1];
        $restoredRevision = $this->cloneRevision($page, $previousRevision, 'published');
        $this->applyRevisionSnapshot($page, $restoredRevision, self::STATUS_PUBLISHED);
        $page->setPublishedAt(new \DateTimeImmutable());
        $page->setUnpublishedAt(null);
        $this->entityManager->flush();

        $this->logger->info('growth_article_restored', [
            'external_id' => $externalId,
            'restored_from_revision' => $previousRevision->getRevisionNumber(),
            'new_revision' => $restoredRevision->getRevisionNumber(),
        ]);

        return $this->buildStatusPayload($page, $this->findActiveDraft($page), 200);
    }

    public function buildPreviewPage(string $externalId): SitePageRevision
    {
        $page = $this->findPage($externalId);
        $draft = $this->findActiveDraft($page);
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

    private function assertSlugAvailable(string $publicSlug, SitePage $currentPage): void
    {
        foreach (self::BLOCKED_PUBLIC_SLUG_PREFIXES as $prefix) {
            if (str_starts_with($publicSlug, $prefix)) {
                throw new ConflictHttpException('This slug is blocked and cannot be published.');
            }
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
        $page->setHeroImage($this->normalizeMediaPath($input->featuredImage));
        $page->setCanonicalUrl($this->normalizeUrl($input->canonicalUrl));
        $page->setCategories($this->sanitizeTerms($input->categories));
        $page->setTags($this->sanitizeTerms($input->tags));
        $page->setPublicationDate($input->publicationDate);
        $page->setAuthorDisplayName($this->sanitizer->sanitizeText($input->authorDisplayName, 255));
        $page->setSourceCampaignId($this->sanitizer->sanitizeText($input->sourceCampaignId, 190));
        $page->setPublicationStatus($status);
    }

    private function applyRevisionSnapshot(SitePage $page, SitePageRevision $revision, string $status): void
    {
        $page->setSlug(self::RESOURCE_PREFIX.$revision->getSlug());
        $page->setTitle($revision->getMetaTitle());
        $page->setMetaDescription($revision->getMetaDescription());
        $page->setHeroTitle($revision->getTitle());
        $page->setHeroIntro($revision->getExcerpt());
        $page->setHeroSideHtml($revision->getContentHtml());
        $page->setHeroImage($revision->getFeaturedImage());
        $page->setCanonicalUrl($revision->getCanonicalUrl());
        $page->setCategories($revision->getCategories());
        $page->setTags($revision->getTags());
        $page->setPublicationDate($revision->getPublicationDate());
        $page->setAuthorDisplayName($revision->getAuthorDisplayName());
        $page->setSourceCampaignId($revision->getSourceCampaignId());
        $page->setPublicationStatus($status);
    }

    private function buildStatusPayload(SitePage $page, ?SitePageRevision $draftRevision, int $httpCode): array
    {
        $publishedRevisions = $this->revisionRepository->findPublishedRevisions($page, 1);
        $publishedRevision = $publishedRevisions[0] ?? null;
        $hasPendingDraft = $draftRevision !== null
            && ($publishedRevision === null || $draftRevision->getRevisionNumber() > $publishedRevision->getRevisionNumber());

        return [
            'http_code' => $httpCode,
            'external_id' => $page->getExternalId(),
            'public_slug' => $this->extractPublicSlug((string) $page->getSlug()),
            'publication_status' => $page->getPublicationStatus(),
            'has_pending_draft' => $hasPendingDraft,
            'draft_revision_number' => $hasPendingDraft ? $draftRevision?->getRevisionNumber() : null,
            'published_revision_number' => $publishedRevision?->getRevisionNumber(),
            'published_at' => $page->getPublishedAt()?->format(DATE_ATOM),
            'unpublished_at' => $page->getUnpublishedAt()?->format(DATE_ATOM),
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
}
