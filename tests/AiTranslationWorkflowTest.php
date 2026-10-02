<?php

namespace App\Tests;

use App\Entity\SitePage;
use App\Entity\SitePageTranslation;
use App\Repository\SitePageTranslationRepository;
use App\Service\I18n\AiTranslationConflictException;
use App\Service\I18n\AiTranslationProviderInterface;
use App\Service\I18n\AiTranslationResourceRegistry;
use App\Service\I18n\AiTranslationResult;
use App\Service\I18n\AiTranslationService;
use App\Service\I18n\AiTranslationValidationException;
use App\Service\I18n\TranslationReviewWorkflow;
use App\Service\TranslationSourceHasher;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\KernelInterface;

final class AiTranslationWorkflowTest extends TestCase
{
    public function testAiGeneratedSitePageTranslationIsNeverPublished(): void
    {
        $page = $this->pageWithFrenchSource();
        $service = $this->service(new ArrayTranslationProvider([
            'slug' => 'erp-software',
            'title' => 'ERP software advisory',
            'seoTitle' => 'ERP software advisory',
            'seoDescription' => 'Advisory for ERP projects.',
            'ogTitle' => 'ERP software advisory',
            'ogDescription' => 'Advisory for ERP projects.',
            'imageAlt' => 'ERP advisory',
            'heroBadge' => 'ERP',
            'heroTitle' => 'ERP software advisory',
            'heroIntro' => '<p>Preserve {{ variable }} and {value}.</p>',
            'heroSideHtml' => '<ul><li>ERP selection</li></ul>',
            'bodyHtml' => '<p>Business-side IT project management %name%.</p>',
            'structuredData' => [
                'corePage' => [
                    'title' => 'ERP software',
                    'items' => ['Scope', 'Selection'],
                ],
            ],
        ]));

        $translation = $service->translateSitePage($page, 'en');

        self::assertSame(SitePageTranslation::STATUS_AI_TRANSLATED, $translation->getTranslationStatus());
        self::assertFalse($translation->isPublished());
        self::assertNull($translation->getPublishedAt());
        self::assertSame('erp-software', $translation->getSlug());
        self::assertNotNull($translation->getSourceContentHash());
        self::assertNotNull($translation->getTranslatedAt());
    }

    public function testReviewedTranslationStillIsNotPublicWithoutPublishedStatus(): void
    {
        $translation = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-software')
            ->setTitle('ERP software')
            ->setTranslationStatus(SitePageTranslation::STATUS_REVIEWED)
            ->setReviewedAt(new \DateTimeImmutable('2026-10-02 12:00:00'));

        self::assertFalse($translation->isPublished());
    }

    public function testOnlyHumanReviewWorkflowCanPublish(): void
    {
        $workflow = new TranslationReviewWorkflow();
        $translation = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-software')
            ->setTitle('ERP software')
            ->setTranslationStatus(SitePageTranslation::STATUS_AI_TRANSLATED);

        $workflow->markToReview($translation);
        self::assertSame(SitePageTranslation::STATUS_TO_REVIEW, $translation->getTranslationStatus());
        $workflow->markReviewed($translation);
        self::assertSame(SitePageTranslation::STATUS_REVIEWED, $translation->getTranslationStatus());
        self::assertFalse($translation->isPublished());
        $workflow->publish($translation);

        self::assertTrue($translation->isPublished());
    }

    public function testSourceHashDetectsOutdatedTranslationAfterFrenchChange(): void
    {
        $page = $this->pageWithFrenchSource();
        $source = $page->getTranslation('fr');
        self::assertInstanceOf(SitePageTranslation::class, $source);
        $hasher = new TranslationSourceHasher();
        $storedHash = $hasher->hashSitePageTranslation($source);
        $target = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-software')
            ->setTitle('ERP software')
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setSourceContentHash($storedHash);

        $source->setHeroTitle('Titre FR modifié');

        self::assertTrue($target->isOutdated($hasher->hashSitePageTranslation($source)));
    }

    public function testSlugCollisionReturnsConflictWithoutOverwrite(): void
    {
        $page = $this->pageWithFrenchSource();
        $otherPage = (new SitePage())->setSlug('other')->setTitle('Other');
        $existing = (new SitePageTranslation())
            ->setLocale('en')
            ->setSlug('erp-software')
            ->setTitle('Existing');
        $otherPage->addTranslation($existing);
        $repository = $this->createMock(SitePageTranslationRepository::class);
        $repository->method('findOneBy')->willReturn($existing);

        $this->expectException(AiTranslationConflictException::class);
        $this->service($this->validProvider(), $repository)->translateSitePage($page, 'en');
    }

    public function testStructuredDataKeysMustBePreserved(): void
    {
        $page = $this->pageWithFrenchSource();
        $provider = new ArrayTranslationProvider([
            'slug' => 'erp-software',
            'title' => 'ERP software',
            'seoTitle' => 'ERP software',
            'seoDescription' => 'ERP software',
            'ogTitle' => null,
            'ogDescription' => null,
            'imageAlt' => null,
            'heroBadge' => 'ERP',
            'heroTitle' => 'ERP software',
            'heroIntro' => '<p>Preserve {{ variable }} and {value}.</p>',
            'heroSideHtml' => '<ul><li>ERP selection</li></ul>',
            'bodyHtml' => '<p>Business-side IT project management %name%.</p>',
            'structuredData' => [
                'wrongKey' => ['title' => 'Broken'],
            ],
        ]);

        $this->expectException(AiTranslationValidationException::class);
        $this->service($provider)->translateSitePage($page, 'en');
    }

    public function testPlaceholdersMustBePreserved(): void
    {
        $page = $this->pageWithFrenchSource();
        $payload = $this->validPayload();
        $payload['heroIntro'] = '<p>Missing placeholders.</p>';

        $this->expectException(AiTranslationValidationException::class);
        $this->service(new ArrayTranslationProvider($payload))->translateSitePage($page, 'en');
    }

    public function testRegistryDocumentsInitialWorkflowScope(): void
    {
        self::assertContains('SitePage', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('Practice', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('Service', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('Projet', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('Team', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('LegalPage', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('HomeSection', AiTranslationResourceRegistry::SUPPORTED_ENTITIES);
        self::assertContains('TeamTranslation.public_profile', AiTranslationResourceRegistry::STRUCTURED_FIELDS);
        self::assertContains('ServiceTranslation.public_narrative', AiTranslationResourceRegistry::STRUCTURED_FIELDS);
    }

    private function pageWithFrenchSource(): SitePage
    {
        $page = (new SitePage())->setSlug('erp-progiciel')->setTitle('ERP progiciel');
        $source = (new SitePageTranslation())
            ->setLocale('fr')
            ->setSlug('erp-progiciel')
            ->setTitle('Conseil ERP')
            ->setSeoTitle('Conseil ERP')
            ->setSeoDescription('Conseil ERP pour PME.')
            ->setHeroBadge('AMOA ERP')
            ->setHeroTitle('Cadrer un projet ERP')
            ->setHeroIntro('<p>Conserver {{ variable }} et {value}.</p>')
            ->setHeroSideHtml('<ul><li>Choix ERP</li></ul>')
            ->setBodyHtml('<p>AMOA SI %name%.</p>')
            ->setStructuredData([
                'corePage' => [
                    'title' => 'ERP',
                    'items' => ['Cadrage', 'Choix'],
                ],
            ])
            ->setTranslationStatus(SitePageTranslation::STATUS_PUBLISHED)
            ->setPublishedAt(new \DateTimeImmutable('2026-10-02 12:00:00'));
        $page->addTranslation($source);

        return $page;
    }

    private function service(?AiTranslationProviderInterface $provider = null, ?SitePageTranslationRepository $repository = null): AiTranslationService
    {
        $kernel = $this->createMock(KernelInterface::class);
        $kernel->method('getProjectDir')->willReturn(dirname(__DIR__));

        return new AiTranslationService(
            $this->createMock(EntityManagerInterface::class),
            $repository ?? $this->createMock(SitePageTranslationRepository::class),
            new TranslationSourceHasher(),
            $provider ?? $this->validProvider(),
            $kernel
        );
    }

    private function validProvider(): ArrayTranslationProvider
    {
        return new ArrayTranslationProvider($this->validPayload());
    }

    /**
     * @return array<string, mixed>
     */
    private function validPayload(): array
    {
        return [
            'slug' => 'erp-software',
            'title' => 'ERP software',
            'seoTitle' => 'ERP software',
            'seoDescription' => 'ERP advisory.',
            'ogTitle' => null,
            'ogDescription' => null,
            'imageAlt' => null,
            'heroBadge' => 'ERP',
            'heroTitle' => 'ERP software',
            'heroIntro' => '<p>Preserve {{ variable }} and {value}.</p>',
            'heroSideHtml' => '<ul><li>ERP selection</li></ul>',
            'bodyHtml' => '<p>IT project advisory %name%.</p>',
            'structuredData' => [
                'corePage' => [
                    'title' => 'ERP',
                    'items' => ['Scope', 'Selection'],
                ],
            ],
        ];
    }
}

final class ArrayTranslationProvider implements AiTranslationProviderInterface
{
    /**
     * @param array<string, mixed> $payload
     */
    public function __construct(private readonly array $payload)
    {
    }

    public function translate(array $sourcePayload, string $targetLocale, array $glossary): AiTranslationResult
    {
        return new AiTranslationResult($this->payload, 'test', 'test-model');
    }
}
