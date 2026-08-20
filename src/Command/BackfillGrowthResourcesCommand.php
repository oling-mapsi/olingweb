<?php

namespace App\Command;

use App\Entity\SitePageRevision;
use App\Repository\SitePageRepository;
use App\Repository\SitePageRevisionRepository;
use App\Service\GrowthContentSanitizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:growth:backfill-resources',
    description: 'Reconstitue un etat Growth local exploitable pour les ressources existantes.',
)]
class BackfillGrowthResourcesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly SitePageRevisionRepository $revisionRepository,
        private readonly GrowthContentSanitizer $sanitizer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pages = $this->sitePageRepository->findBy([], ['slug' => 'ASC']);

        $handled = 0;
        foreach ($pages as $page) {
            $slug = (string) $page->getSlug();
            if (!str_starts_with($slug, 'ressource-')) {
                continue;
            }

            $publicSlug = substr($slug, strlen('ressource-'));
            if ($publicSlug === false || $publicSlug === '') {
                continue;
            }

            $publicationDate = $page->getPublicationDate() ?? new \DateTimeImmutable('2026-08-08 12:00:00');
            $externalId = $page->getExternalId() ?: 'growth-local-'.$publicSlug;

            $page
                ->setExternalId($externalId)
                ->setPublicationStatus('published')
                ->setPublicationDate($publicationDate)
                ->setPublishedAt($page->getPublishedAt() ?? $publicationDate)
                ->setUnpublishedAt(null)
                ->setAuthorDisplayName($page->getAuthorDisplayName() ?: 'OLING')
                ->setSourceCampaignId($page->getSourceCampaignId() ?: 'growth-local-backfill');

            if ($this->revisionRepository->findPublishedRevisions($page, 1) === []) {
                $revision = (new SitePageRevision())
                    ->setSitePage($page)
                    ->setRevisionNumber($this->revisionRepository->nextRevisionNumber($page))
                    ->setRevisionState('published')
                    ->setTitle($this->sanitizer->sanitizeText($page->getHeroTitle() ?: $page->getTitle(), 255))
                    ->setSlug($publicSlug)
                    ->setExcerpt($this->sanitizer->sanitizeHtml($page->getHeroIntro()))
                    ->setContentHtml($this->sanitizer->sanitizeHtml($page->getHeroSideHtml() ?: $page->getBodyHtml()))
                    ->setMetaTitle($this->sanitizer->sanitizeText($page->getTitle(), 255))
                    ->setMetaDescription($this->sanitizer->sanitizeText($page->getMetaDescription(), 2000))
                    ->setCanonicalUrl($page->getCanonicalUrl())
                    ->setFeaturedImage($page->getHeroImage())
                    ->setCategories($page->getCategories())
                    ->setTags($page->getTags())
                    ->setPublicationDate($publicationDate)
                    ->setStatus('published')
                    ->setAuthorDisplayName($page->getAuthorDisplayName() ?: 'OLING')
                    ->setSourceCampaignId($page->getSourceCampaignId() ?: 'growth-local-backfill')
                    ->setCreatedAt(new \DateTimeImmutable());

                $this->entityManager->persist($revision);
            }

            ++$handled;
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d ressource(s) Growth reconstituee(s).', $handled));

        return Command::SUCCESS;
    }
}
