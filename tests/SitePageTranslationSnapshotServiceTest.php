<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageRepository;
use App\Repository\SitePageTranslationRepository;
use App\Service\I18n\SitePageTranslationSnapshotService;
use App\Service\TranslationSourceHasher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class SitePageTranslationSnapshotServiceTest extends TestCase
{
    public function testExportKeepsStableSourceSlugStatusHashAndStructuredData(): void
    {
        $page = $this->page('erp-progiciel');
        $translation = $this->translation('en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED);
        $translation->setSourceContentHash('hash-123')->setStructuredData(['corePage' => ['title' => 'ERP']]);
        $page->addTranslation($translation);

        $service = $this->service([$page], [$translation]);
        $payload = $service->export('en', [SitePageTranslation::STATUS_PUBLISHED]);

        self::assertSame('SitePageTranslation', $payload['entity']);
        self::assertSame('en', $payload['locale']);
        self::assertCount(1, $payload['translations']);
        self::assertSame('erp-progiciel', $payload['translations'][0]['source']['slug']);
        self::assertSame('erp-software', $payload['translations'][0]['slug']);
        self::assertSame(SitePageTranslation::STATUS_PUBLISHED, $payload['translations'][0]['status']);
        self::assertSame('hash-123', $payload['translations'][0]['sourceContentHash']);
        self::assertSame(['corePage' => ['title' => 'ERP']], $payload['translations'][0]['structuredData']);
    }

    public function testImportCreatesThenBecomesIdempotent(): void
    {
        $page = $this->page('erp-progiciel');
        $service = $this->service([$page], []);
        $path = $this->writePayload('en', [$this->row('erp-progiciel', 'en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED)]);

        $first = $service->import('en', $path);
        $second = $service->import('en', $path);
        $translation = $page->getTranslation('en');

        self::assertSame(1, $first['created']);
        self::assertSame(0, $first['conflict']);
        self::assertSame(0, $second['created']);
        self::assertSame(1, $second['unchanged']);
        self::assertInstanceOf(SitePageTranslation::class, $translation);
        self::assertSame('erp-software', $translation->getSlug());
        self::assertSame(SitePageTranslation::STATUS_PUBLISHED, $translation->getTranslationStatus());
        self::assertSame('hash-123', $translation->getSourceContentHash());
        self::assertSame(['corePage' => ['title' => 'ERP software']], $translation->getStructuredData());
    }

    public function testImportConflictsWhenExistingTranslationDiffersWithoutOverwrite(): void
    {
        $page = $this->page('erp-progiciel');
        $page->addTranslation($this->translation('en', 'old-slug', SitePageTranslation::STATUS_REVIEWED));
        $service = $this->service([$page], []);
        $path = $this->writePayload('en', [$this->row('erp-progiciel', 'en', 'erp-software', SitePageTranslation::STATUS_PUBLISHED)]);

        $result = $service->import('en', $path);

        self::assertSame(1, $result['conflict']);
        self::assertSame('old-slug', $page->getTranslation('en')?->getSlug());
    }

    /**
     * @param SitePage[] $pages
     * @param SitePageTranslation[] $exportTranslations
     */
    private function service(array $pages, array $exportTranslations): SitePageTranslationSnapshotService
    {
        $pageRepository = $this->createMock(SitePageRepository::class);
        $pageRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($pages): ?SitePage {
            foreach ($pages as $page) {
                if (($criteria['slug'] ?? null) === $page->getSlug()) {
                    return $page;
                }
            }

            return null;
        });

        $translationRepository = $this->createMock(SitePageTranslationRepository::class);
        $translationRepository->method('findBy')->willReturn($exportTranslations);
        $translationRepository->method('findOneByPageAndLocale')->willReturnCallback(
            static fn (SitePage $page, string $locale): ?SitePageTranslation => $page->getTranslation($locale)
        );
        $translationRepository->method('findOneBy')->willReturnCallback(function (array $criteria) use ($pages): ?SitePageTranslation {
            foreach ($pages as $page) {
                $translation = $page->getTranslation((string) ($criteria['locale'] ?? ''));
                if ($translation instanceof SitePageTranslation && $translation->getSlug() === ($criteria['slug'] ?? null)) {
                    return $translation;
                }
            }

            return null;
        });

        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn(dirname(__DIR__));

        return new SitePageTranslationSnapshotService(
            $pageRepository,
            $translationRepository,
            $this->createMock(EntityManagerInterface::class),
            new TranslationSourceHasher(),
            $kernel
        );
    }

    private function page(string $slug): SitePage
    {
        $page = (new SitePage())->setSlug($slug)->setTitle('Page');
        $page->addTranslation(
            (new SitePageTranslation())
                ->setLocale('fr')
                ->setSlug($slug)
                ->setTitle('Conseil ERP')
                ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
                ->setPublishedAt(new \DateTimeImmutable('2026-10-02T10:00:00+00:00'))
        );

        return $page;
    }

    private function translation(string $locale, string $slug, string $status): SitePageTranslation
    {
        return (new SitePageTranslation())
            ->setLocale($locale)
            ->setSlug($slug)
            ->setTitle('ERP software')
            ->setSeoTitle('ERP software')
            ->setSeoDescription('ERP advisory.')
            ->setHeroTitle('ERP software')
            ->setTranslationStatus($status)
            ->setReviewedAt(new \DateTimeImmutable('2026-10-02T10:00:00+00:00'))
            ->setPublishedAt($status === SitePageTranslation::STATUS_PUBLISHED ? new \DateTimeImmutable('2026-10-02T10:00:00+00:00') : null);
    }

    /**
     * @param array<int, array<string, mixed>> $rows
     */
    private function writePayload(string $locale, array $rows): string
    {
        $path = sys_get_temp_dir().'/site_pages.'.$locale.'.'.bin2hex(random_bytes(4)).'.json';
        file_put_contents($path, json_encode([
            'entity' => 'SitePageTranslation',
            'locale' => $locale,
            'exportedAt' => '2026-10-02T10:00:00+00:00',
            'translations' => $rows,
        ], JSON_THROW_ON_ERROR));

        return $path;
    }

    /**
     * @return array<string, mixed>
     */
    private function row(string $sourceSlug, string $locale, string $slug, string $status): array
    {
        return [
            'source' => ['entity' => 'SitePage', 'slug' => $sourceSlug, 'externalId' => null],
            'locale' => $locale,
            'slug' => $slug,
            'title' => 'ERP software',
            'seoTitle' => 'ERP software',
            'seoDescription' => 'ERP advisory.',
            'ogTitle' => null,
            'ogDescription' => null,
            'imageAlt' => null,
            'heroBadge' => null,
            'heroTitle' => 'ERP software',
            'heroIntro' => null,
            'heroSideHtml' => null,
            'bodyHtml' => null,
            'structuredData' => ['corePage' => ['title' => 'ERP software']],
            'status' => $status,
            'sourceContentHash' => 'hash-123',
            'sourceUpdatedAt' => null,
            'translatedAt' => null,
            'reviewedAt' => '2026-10-02T10:00:00+00:00',
            'publishedAt' => $status === SitePageTranslation::STATUS_PUBLISHED ? '2026-10-02T10:00:00+00:00' : null,
            'unpublishedAt' => null,
            'isOutdated' => false,
        ];
    }
}
