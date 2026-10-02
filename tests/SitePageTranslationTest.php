<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Service\TranslationSourceHasher;
use PHPUnit\Framework\TestCase;

class SitePageTranslationTest extends TestCase
{
    public function testPageCanRetrieveFrenchTranslation(): void
    {
        $page = $this->createPage();
        $translation = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('erp-progiciel')
            ->setTitle('ERP')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-10-02 10:00:00'));

        $page->addTranslation($translation);

        self::assertSame($translation, $page->getTranslation('fr'));
        self::assertNull($page->getTranslation('en'));
        self::assertSame($translation, $page->getPublishedTranslation('fr'));
    }

    public function testWorkflowRejectsUnsupportedStatus(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        (new SitePageTranslation())->setTranslationStatus('outdated');
    }

    public function testHashIsStableForSameContentAndTimestampsAreIgnored(): void
    {
        $hasher = new TranslationSourceHasher();
        $page = $this->createPage();

        $firstHash = $hasher->hashSitePage($page);
        $page->setPublishedAt(new \DateTimeImmutable('2026-10-02 10:00:00'));
        $page->setUnpublishedAt(new \DateTimeImmutable('2026-10-03 10:00:00'));

        self::assertSame($firstHash, $hasher->hashSitePage($page));
    }

    public function testHashChangesWhenSignificantContentChanges(): void
    {
        $hasher = new TranslationSourceHasher();
        $page = $this->createPage();

        $firstHash = $hasher->hashSitePage($page);
        $page->setHeroIntro('Texte modifie');

        self::assertNotSame($firstHash, $hasher->hashSitePage($page));
    }

    public function testOutdatedIsComputedFromSourceHash(): void
    {
        $hasher = new TranslationSourceHasher();
        $page = $this->createPage();
        $sourceHash = $hasher->hashSitePage($page);

        $translation = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-consulting')
            ->setTitle('ERP consulting')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setSourceContentHash($sourceHash);

        self::assertFalse($translation->isOutdated($sourceHash));

        $page->setHeroTitle('Nouveau titre ERP');

        self::assertTrue($translation->isOutdated($hasher->hashSitePage($page)));
    }

    public function testStructuredDataCanCarryLandingNarrative(): void
    {
        $translation = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('erp-progiciel')
            ->setTitle('ERP')
            ->setStructuredData([
                'landingNarrative' => [
                    'heroTitle' => 'DATABASE VALUE',
                    'supportLinks' => [
                        ['href' => '/amoa-si', 'label' => 'AMOA SI'],
                    ],
                ],
            ]);

        self::assertSame('DATABASE VALUE', $translation->getStructuredData()['landingNarrative']['heroTitle']);
        self::assertSame('/amoa-si', $translation->getStructuredData()['landingNarrative']['supportLinks'][0]['href']);
    }

    private function createPage(): SitePage
    {
        return (new SitePage())
            ->setSlug('erp-progiciel')
            ->setTitle('AMOA ERP | OLING')
            ->setMetaDescription('Cadrage ERP')
            ->setHeroBadge('ERP')
            ->setHeroTitle('AMOA ERP')
            ->setHeroIntro('Piloter un projet ERP')
            ->setHeroSideHtml('<p>Side</p>')
            ->setBodyHtml('<p>Body</p>')
            ->setCanonicalUrl('https://oling.fr/erp-progiciel')
            ->setCategories(['ERP', 'AMOA'])
            ->setTags(['erp', 'progiciel']);
    }
}
