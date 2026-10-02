<?php

namespace App\Tests;

use App\Entity\LocalizedSlugHistory;
use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Service\SitePageTranslationSynchronizer;
use App\Service\TranslationSourceHasher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class SitePageTranslationSynchronizerTest extends TestCase
{
    public function testEnsureFrenchTranslationCreatesExactlyOneTranslation(): void
    {
        $persisted = [];
        $synchronizer = $this->createSynchronizer($persisted);
        $page = $this->createPage('erp-progiciel');

        $translation = $synchronizer->ensureFrenchTranslation($page);
        $sameTranslation = $synchronizer->ensureFrenchTranslation($page);

        self::assertSame($translation, $sameTranslation);
        self::assertCount(1, $page->getTranslations());
        self::assertSame('fr', $translation->getLocale());
        self::assertSame('erp-progiciel', $translation->getSlug());
    }

    public function testSyncFrenchTranslationUpdatesLegacyFields(): void
    {
        $persisted = [];
        $synchronizer = $this->createSynchronizer($persisted);
        $page = $this->createPage('erp-progiciel');
        $translation = $synchronizer->ensureFrenchTranslation($page);
        $translation
            ->setTitle('Nouveau titre')
            ->setSeoDescription('Nouvelle meta')
            ->setHeroTitle('Nouveau hero')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-10-02 12:00:00'));

        $synchronizer->syncFrenchTranslationToLegacyFields($page);

        self::assertSame('Nouveau titre', $page->getTitle());
        self::assertSame('Nouvelle meta', $page->getMetaDescription());
        self::assertSame('Nouveau hero', $page->getHeroTitle());
        self::assertSame(SitePageTranslation::STATUS_PUBLISHED, $page->getPublicationStatus());
        self::assertNotNull($translation->getSourceContentHash());
    }

    public function testSlugChangeCreatesHistoryForPublishedPage(): void
    {
        $persisted = [];
        $synchronizer = $this->createSynchronizer($persisted);
        $page = $this->createPage('old-slug');
        $this->assignPageId($page, 123);
        $translation = $synchronizer->ensureFrenchTranslation($page);
        $translation
            ->setSlug('new-slug')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-10-02 12:00:00'));

        $synchronizer->syncFrenchTranslationToLegacyFields($page);

        $histories = array_values(array_filter($persisted, static fn ($item): bool => $item instanceof LocalizedSlugHistory));
        self::assertCount(1, $histories);
        self::assertSame('old-slug', $histories[0]->getOldSlug());
        self::assertSame('new-slug', $histories[0]->getNewSlug());
        self::assertSame('fr', $histories[0]->getLocale());
    }

    public function testUnchangedSlugCreatesNoHistory(): void
    {
        $persisted = [];
        $synchronizer = $this->createSynchronizer($persisted);
        $page = $this->createPage('stable-slug');
        $translation = $synchronizer->ensureFrenchTranslation($page);
        $translation->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED);

        $synchronizer->syncFrenchTranslationToLegacyFields($page);

        self::assertCount(0, array_filter($persisted, static fn ($item): bool => $item instanceof LocalizedSlugHistory));
    }

    public function testExistingNonFrenchTranslationCanBecomeOutdatedWithoutStatusChange(): void
    {
        $hasher = new TranslationSourceHasher();
        $page = $this->createPage('erp-progiciel');
        $sourceHash = $hasher->hashSitePage($page);
        $enTranslation = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-consulting')
            ->setTitle('ERP consulting')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setSourceContentHash($sourceHash);

        $page->setTitle('Titre source modifie');
        $currentHash = $hasher->hashSitePage($page);

        self::assertTrue($enTranslation->isOutdated($currentHash));
        self::assertSame(SitePageTranslation::STATUS_PUBLISHED, $enTranslation->getTranslationStatus());
    }

    /**
     * @param array<int, object> $persisted
     */
    private function createSynchronizer(array &$persisted): SitePageTranslationSynchronizer
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager
            ->method('persist')
            ->willReturnCallback(static function (object $object) use (&$persisted): void {
                $persisted[] = $object;
            });

        return new SitePageTranslationSynchronizer($entityManager, new TranslationSourceHasher());
    }

    private function createPage(string $slug): SitePage
    {
        return (new SitePage())
            ->setSlug($slug)
            ->setTitle('Titre source')
            ->setMetaDescription('Meta source')
            ->setHeroBadge('Badge')
            ->setHeroTitle('Hero source')
            ->setHeroIntro('Intro source')
            ->setHeroSideHtml('<p>Side</p>')
            ->setBodyHtml('<p>Body</p>')
            ->setPublicationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-10-02 10:00:00'));
    }

    private function assignPageId(SitePage $page, int $id): void
    {
        $property = new \ReflectionProperty(SitePage::class, 'id');
        $property->setAccessible(true);
        $property->setValue($page, $id);
    }
}
