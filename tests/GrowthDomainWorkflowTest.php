<?php

namespace App\Tests;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Enum\GrowthCampaignStatus;
use App\Enum\GrowthContentStatus;
use App\Enum\GrowthDestination;
use App\Enum\GrowthPublicationStatus;
use App\Service\Growth\GrowthAuditLogger;
use App\Service\Growth\GrowthContentGeneratorInterface;
use App\Service\Growth\GrowthPublisherInterface;
use App\Service\Growth\GrowthPublisherRegistry;
use App\Service\Growth\GrowthWorkflow;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class GrowthDomainWorkflowTest extends TestCase
{
    public function testReviewApproveAndPublishRequireApprovedContent(): void
    {
        $campaign = (new GrowthCampaign())->setTitle('Campagne');
        $content = (new GrowthContent())
            ->setCampaign($campaign)
            ->setTitle('Titre')
            ->setSlug('titre')
            ->setExcerpt('Extrait')
            ->setContentHtml('<p>Texte</p>')
            ->setMetaTitle('Meta')
            ->setMetaDescription('Description');
        $campaign->addContent($content);

        $publisher = new class implements GrowthPublisherInterface {
            public bool $called = false;
            public function supports(GrowthDestination $destination): bool { return $destination === GrowthDestination::OLING_PUBLIC; }
            public function publish(\App\Entity\GrowthPublication $publication): void
            {
                $this->called = true;
                $publication->setStatus(GrowthPublicationStatus::PUBLISHED)->setPublishedAt(new \DateTimeImmutable());
            }
        };

        $workflow = new GrowthWorkflow(
            $this->entityManager(),
            $this->createStub(GrowthContentGeneratorInterface::class),
            new GrowthPublisherRegistry([$publisher]),
            $this->auditLogger()
        );

        $this->expectException(\RuntimeException::class);
        $workflow->publish($campaign, GrowthDestination::OLING_PUBLIC, null);

        $content->setStatus(GrowthContentStatus::GENERATED);
        $workflow->review($campaign, $content, null);
        $workflow->approve($campaign, $content, null);
        $publication = $workflow->publish($campaign, GrowthDestination::OLING_PUBLIC, null);

        self::assertTrue($publisher->called);
        self::assertSame(GrowthPublicationStatus::PUBLISHED, $publication->getStatus());
    }

    public function testApproveRequiresReviewedContent(): void
    {
        $campaign = (new GrowthCampaign())->setTitle('Campagne');
        $content = (new GrowthContent())->setCampaign($campaign)->setStatus(GrowthContentStatus::GENERATED);
        $campaign->addContent($content);

        $workflow = new GrowthWorkflow(
            $this->entityManager(),
            $this->createStub(GrowthContentGeneratorInterface::class),
            new GrowthPublisherRegistry([]),
            $this->auditLogger()
        );

        $this->expectException(\RuntimeException::class);
        $workflow->approve($campaign, $content, null);
    }

    public function testDeleteIsAuditedAndRemovesCampaign(): void
    {
        $campaign = (new GrowthCampaign())->setTitle('A supprimer');
        $entityManager = $this->entityManager();
        $entityManager->expects(self::once())->method('remove')->with($campaign);

        $workflow = new GrowthWorkflow(
            $entityManager,
            $this->createStub(GrowthContentGeneratorInterface::class),
            new GrowthPublisherRegistry([]),
            $this->auditLogger()
        );

        $workflow->delete($campaign, null);
    }

    public function testPublishedCampaignDeleteArchivesInsteadOfRemoving(): void
    {
        $campaign = (new GrowthCampaign())->setTitle('Publiee');
        $publication = (new \App\Entity\GrowthPublication())->setStatus(GrowthPublicationStatus::PUBLISHED);
        $campaign->addPublication($publication);
        $entityManager = $this->entityManager();
        $entityManager->expects(self::never())->method('remove');

        $workflow = new GrowthWorkflow(
            $entityManager,
            $this->createStub(GrowthContentGeneratorInterface::class),
            new GrowthPublisherRegistry([]),
            $this->auditLogger()
        );

        $workflow->delete($campaign, null);

        self::assertSame(GrowthCampaignStatus::ARCHIVED, $campaign->getStatus());
    }


    private function entityManager(): EntityManagerInterface&\PHPUnit\Framework\MockObject\MockObject
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('persist');
        $entityManager->method('flush');

        return $entityManager;
    }

    private function auditLogger(): GrowthAuditLogger
    {
        return $this->getMockBuilder(GrowthAuditLogger::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['log'])
            ->getMock();
    }
}
