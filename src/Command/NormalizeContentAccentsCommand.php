<?php

namespace App\Command;

use App\Repository\SitePageRepository;
use App\Repository\SitePageRevisionRepository;
use App\Service\FrenchAccentNormalizer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:content:normalize-accents',
    description: 'Corrige les accents manquants dans les contenus éditoriaux stockés en base.',
)]
class NormalizeContentAccentsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly SitePageRevisionRepository $revisionRepository,
        private readonly FrenchAccentNormalizer $normalizer,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pageUpdates = 0;
        $revisionUpdates = 0;

        foreach ($this->sitePageRepository->findBy([], ['id' => 'ASC']) as $page) {
            $changed = false;
            $changed = $this->normalizeField($page, 'title', $page->getTitle(), $pageUpdates) || $changed;
            $changed = $this->normalizeField($page, 'metaDescription', $page->getMetaDescription(), $pageUpdates) || $changed;
            $changed = $this->normalizeField($page, 'heroBadge', $page->getHeroBadge(), $pageUpdates) || $changed;
            $changed = $this->normalizeField($page, 'heroTitle', $page->getHeroTitle(), $pageUpdates) || $changed;
            $changed = $this->normalizeField($page, 'heroIntro', $page->getHeroIntro(), $pageUpdates) || $changed;
            $changed = $this->normalizeField($page, 'heroSideHtml', $page->getHeroSideHtml(), $pageUpdates) || $changed;

            $normalizedBody = $this->normalizer->normalizeStructured($page->getBodyHtml());
            if ($normalizedBody !== $page->getBodyHtml()) {
                $page->setBodyHtml($normalizedBody);
                ++$pageUpdates;
                $changed = true;
            }

            if ($changed) {
                $this->entityManager->persist($page);
            }
        }

        foreach ($this->revisionRepository->findBy([], ['id' => 'ASC']) as $revision) {
            $changed = false;
            $changed = $this->normalizeRevisionField($revision, 'title', $revision->getTitle(), $revisionUpdates) || $changed;
            $changed = $this->normalizeRevisionField($revision, 'excerpt', $revision->getExcerpt(), $revisionUpdates) || $changed;
            $changed = $this->normalizeRevisionField($revision, 'contentHtml', $revision->getContentHtml(), $revisionUpdates) || $changed;
            $changed = $this->normalizeRevisionField($revision, 'metaTitle', $revision->getMetaTitle(), $revisionUpdates) || $changed;
            $changed = $this->normalizeRevisionField($revision, 'metaDescription', $revision->getMetaDescription(), $revisionUpdates) || $changed;
            $changed = $this->normalizeRevisionField($revision, 'authorDisplayName', $revision->getAuthorDisplayName(), $revisionUpdates) || $changed;

            if ($changed) {
                $this->entityManager->persist($revision);
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            '%d champ(s) mis à jour sur site_page, %d champ(s) mis à jour sur site_page_revision.',
            $pageUpdates,
            $revisionUpdates
        ));

        return Command::SUCCESS;
    }

    private function normalizeField(object $entity, string $field, ?string $value, int &$counter): bool
    {
        $normalized = $this->normalizer->normalize($value);
        if ($normalized === $value) {
            return false;
        }

        $setter = 'set'.ucfirst($field);
        $entity->{$setter}($normalized);
        ++$counter;

        return true;
    }

    private function normalizeRevisionField(object $entity, string $field, string $value, int &$counter): bool
    {
        $normalized = $this->fieldUsesStructuredNormalization($field)
            ? $this->normalizer->normalizeStructured($value)
            : $this->normalizer->normalize($value);

        if ($normalized === $value) {
            return false;
        }

        $setter = 'set'.ucfirst($field);
        $entity->{$setter}($normalized);
        ++$counter;

        return true;
    }

    private function fieldUsesStructuredNormalization(string $field): bool
    {
        return $field === 'contentHtml';
    }
}
