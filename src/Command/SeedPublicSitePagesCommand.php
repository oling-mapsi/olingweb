<?php

namespace App\Command;

use App\Entity\SitePage;
use App\Repository\SitePageRepository;
use App\Service\PublicSiteConfig;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:content:seed-public-pages',
    description: 'Cree ou complete les pages editoriales publiques necessaires aux hubs et details modernises.',
)]
class SeedPublicSitePagesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly PublicSiteConfig $publicSiteConfig,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $seeded = 0;

        foreach ($this->buildDefinitions() as $definition) {
            $page = $this->sitePageRepository->findOneBy(['slug' => $definition['slug']]) ?? new SitePage();
            $isNew = $page->getId() === null;

            if ($isNew) {
                $page->setSlug($definition['slug']);
                $this->entityManager->persist($page);
            }

            $this->hydrateIfEmpty($page, $definition);

            if ($isNew) {
                ++$seeded;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf('%d page(s) creee(s). Les pages existantes ont ete completees sans ecrasement.', $seeded));

        return Command::SUCCESS;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildDefinitions(): array
    {
        $definitions = [
            [
                'slug' => 'services',
                'title' => 'Services | OLING',
                'metaDescription' => 'Panorama des services OLING pour PME, PMI et ETI : AMOA ERP, gouvernance, conformite, RGPD, risques, cybers et IA.',
                'heroBadge' => 'Services OLING',
                'heroTitle' => 'Des services activables pour cadrer, securiser et remettre en execution',
                'heroIntro' => 'Chaque service OLING relie un probleme concret de direction a un niveau d’intervention clair: arbitrage, AMOA, gouvernance, conformite, risques, donnees et IA.',
            ],
            [
                'slug' => 'expertises-index',
                'title' => 'Expertises | OLING',
                'metaDescription' => 'Decouvrez les expertises OLING pour les PME, PMI et ETI : transformation SI, AMOA ERP, organisation, data, IA, cybersecurite et conformite.',
                'heroBadge' => 'Expertises OLING',
                'heroTitle' => 'AMO ERP, risques, conformite, RGPD, IA: les expertises qui tiennent les transformations',
                'heroIntro' => 'OLING structure son accompagnement autour des sujets qui exposent directement la direction: arbitrages SI, projets ERP, conformite, gouvernance des donnees, resilience et usages IA.',
                'bodyHtml' => json_encode([
                    'highlights' => ['AMO ERP et applications metiers', 'Risques, cyber et resilience', 'RGPD, gouvernance et IA'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
            [
                'slug' => 'secteurs-index',
                'title' => 'Secteurs | OLING',
                'metaDescription' => 'OLING accompagne les PME, PMI, ETI et organisations regulees sur leurs transformations SI, ERP, conformite, cybersecurite et usages IA.',
                'heroBadge' => 'Secteurs',
                'heroTitle' => 'Des trajectoires de transformation adaptees au terrain, aux equipes et au niveau d’exposition',
                'heroIntro' => 'OLING intervient avec un cadre commun, mais jamais avec une recette unique. Les arbitrages ne sont pas les memes selon la pression operationnelle, le niveau de regulation ou la maturite SI.',
                'bodyHtml' => json_encode([
                    'highlights' => ['PME, PMI et ETI', 'Industrie, services, organisations regulees', 'Approche terrain, risques et execution'],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ],
        ];

        foreach ($this->publicSiteConfig->getExpertisePages() as $slug => $page) {
            $definitions[] = [
                'slug' => 'expertise-' . $slug,
                'title' => $page['seoTitle'],
                'metaDescription' => $page['metaDescription'],
                'heroBadge' => $page['eyebrow'],
                'heroTitle' => $page['title'],
                'heroIntro' => $page['intro'],
                'bodyHtml' => json_encode([
                    'situations' => $page['situations'] ?? [],
                    'interventions' => $page['interventions'] ?? [],
                    'deliverables' => $page['deliverables'] ?? [],
                    'linkedServices' => $page['linkedServices'] ?? [],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        foreach ($this->publicSiteConfig->getSectorPages() as $slug => $page) {
            $definitions[] = [
                'slug' => 'secteur-' . $slug,
                'title' => $page['seoTitle'],
                'metaDescription' => $page['metaDescription'],
                'heroBadge' => $page['eyebrow'],
                'heroTitle' => $page['title'],
                'heroIntro' => $page['intro'],
                'bodyHtml' => json_encode([
                    'issues' => $page['issues'] ?? [],
                    'linkedExpertises' => $page['linkedExpertises'] ?? [],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ];
        }

        return $definitions;
    }

    /**
     * @param array<string, mixed> $definition
     */
    private function hydrateIfEmpty(SitePage $page, array $definition): void
    {
        if (!$page->getTitle()) {
            $page->setTitle((string) $definition['title']);
        }

        if (!$page->getMetaDescription()) {
            $page->setMetaDescription((string) $definition['metaDescription']);
        }

        if (!$page->getHeroBadge()) {
            $page->setHeroBadge((string) $definition['heroBadge']);
        }

        if (!$page->getHeroTitle()) {
            $page->setHeroTitle((string) $definition['heroTitle']);
        }

        if (!$page->getHeroIntro()) {
            $page->setHeroIntro((string) $definition['heroIntro']);
        }

        if (($definition['bodyHtml'] ?? null) && !$page->getBodyHtml()) {
            $page->setBodyHtml((string) $definition['bodyHtml']);
        }
    }
}
