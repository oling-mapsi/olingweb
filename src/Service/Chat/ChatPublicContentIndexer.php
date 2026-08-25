<?php

namespace App\Service\Chat;

use App\Entity\ChatPublicDocument;
use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Repository\SitePageRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ChatPublicContentIndexer
{
    private const SYNONYMS = [
        'erp' => ['progiciel', 'sage x3', 'sage', 'sap', 's4hana', 'divalto', 'cegid'],
        'gmao' => ['maintenance', 'actifs', 'equipements', 'parc', 'interventions', 'ordres de travail'],
        'crm' => ['relation client', 'ventes', 'commercial', 'salesforce'],
        'sirh' => ['rh', 'paie', 'gestion des temps', 'ressources humaines'],
        'si finance' => ['finance', 'comptabilite', 'budget', 'facturation', 'reporting'],
        'rfe' => ['reforme facturation electronique', 'facturation electronique'],
        'rgpd' => ['dpo', 'dpd', 'cnil', 'registre', 'dpia', 'aipd', 'donnees personnelles'],
        'cyber' => ['iso 27001', 'ssi', 'nis2', 'dora', 'securite'],
        'pca pra' => ['continuite', 'reprise', 'resilience', 'iso 22301'],
        'data bi' => ['power bi', 'reporting', 'analytique', 'decisionnel'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SitePageRepository $sitePageRepository,
        private readonly PracticeRepository $practiceRepository,
        private readonly ServicesRepository $servicesRepository,
        private readonly ProjetRepository $projetRepository,
        private readonly TeamRepository $teamRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array{indexed:int,ignored:list<string>,references_without_safe_summary:list<string>}
     */
    public function rebuild(): array
    {
        $ignored = [];
        $referencesWithoutSafeSummary = [];

        $this->entityManager->createQuery('DELETE FROM App\Entity\ChatPublicDocument d')->execute();

        $documents = [];

        foreach ($this->sitePageRepository->findAll() as $page) {
            $status = $page->getPublicationStatus();
            if ($status !== null && $status !== 'published') {
                $ignored[] = 'site_page:'.$page->getSlug();
                continue;
            }

            $text = trim($this->plain($page->getTitle()).' '.$this->plain($page->getMetaDescription()).' '.$this->plain($page->getHeroTitle()).' '.$this->plain($page->getHeroIntro()).' '.$this->plain($page->getBodyHtml()));
            if ($text === '') {
                $ignored[] = 'site_page:'.$page->getSlug();
                continue;
            }

            $documents[] = $this->newDocument(
                'page',
                $page->getId(),
                (string) $page->getTitle(),
                $text,
                $this->resolvePageUrl((string) $page->getSlug()),
                array_merge($page->getCategories(), $page->getTags()),
                false,
                $page->getHeroImage()
            );
        }

        foreach ($this->practiceRepository->findAll() as $practice) {
            $text = trim($this->plain($practice->getDesignation()).' '.$this->plain($practice->getIntroductionShort()).' '.$this->plain($practice->getDescription()).' '.$this->plain($practice->getDescriptionShort()));
            if ($text === '') {
                $ignored[] = 'practice:'.$practice->getSlug();
                continue;
            }

            $keywords = array_merge($practice->getTags() ?? [], [$practice->getDesignation(), $practice->getDesignationShort(), $practice->getH1Title()]);
            $documents[] = $this->newDocument(
                'expertise',
                $practice->getId(),
                (string) $practice->getDesignation(),
                $text,
                $this->urlGenerator->generate('practice_home', ['slug' => $practice->getSlug()]),
                $keywords,
                false,
                $practice->getImage1() ?: $practice->getImage2()
            );
        }

        foreach ($this->servicesRepository->findAll() as $service) {
            if (!$service->getPractice()) {
                $ignored[] = 'service:'.($service->getSlug() ?? 'unknown');
                continue;
            }

            $parts = [
                $service->getDesignation(),
                $service->getDesignationShort(),
                $service->getIntroductionShort(),
                $service->getDescriptionShort(),
                $service->getDescription(),
                $service->getPractice()?->getDesignation(),
            ];
            foreach ($service->getTeams() as $team) {
                $parts[] = $team->getNoncomplet();
                $parts[] = $team->getTitre();
            }

            $text = trim($this->plain(implode(' ', array_filter($parts))));
            if ($text === '') {
                $ignored[] = 'service:'.$service->getSlug();
                continue;
            }

            $keywords = [$service->getDesignation(), $service->getDesignationShort(), $service->getPractice()?->getDesignation()];
            foreach ($service->getTeams() as $team) {
                $keywords[] = $team->getNoncomplet();
            }

            $documents[] = $this->newDocument(
                'service',
                $service->getId(),
                (string) $service->getDesignation(),
                $text,
                $this->urlGenerator->generate('service', [
                    'practice' => $service->getPractice()->getSlug(),
                    'slug' => $service->getSlug(),
                ]),
                $keywords,
                false,
                $service->getImageHero() ?: $service->getImage1() ?: $service->getImage2()
            );
        }

        foreach ($this->projetRepository->findAll() as $project) {
            $safeSummary = $this->buildSafeProjectSummary($project);
            if ($safeSummary === null) {
                $referencesWithoutSafeSummary[] = 'project:'.($project->getId() ?? 'unknown');
                $ignored[] = 'project:'.($project->getId() ?? 'unknown');
                continue;
            }

            $documents[] = $this->newDocument(
                'reference',
                $project->getId(),
                $safeSummary['title'],
                $safeSummary['text'],
                $this->urlGenerator->generate('projets'),
                $safeSummary['keywords'],
                true,
                $project->getImageHero() ?: $project->getImage()
            );
        }

        foreach ($this->teamRepository->findAll() as $member) {
            $parts = [
                $member->getNoncomplet(),
                $member->getTitre(),
                $member->getShortcv(),
            ];
            foreach ($member->getServices() as $service) {
                $parts[] = $service->getDesignation();
                $parts[] = $service->getPractice()?->getDesignation();
            }

            $text = trim($this->plain(implode(' ', array_filter($parts))));
            if ($text === '') {
                $ignored[] = 'team:'.($member->getId() ?? 'unknown');
                continue;
            }

            $keywords = [$member->getNoncomplet(), $member->getTitre()];
            foreach ($member->getServices() as $service) {
                $keywords[] = $service->getDesignation();
            }

            $documents[] = $this->newDocument(
                'team',
                $member->getId(),
                trim((string) $member->getNoncomplet().' '.(string) $member->getTitre()),
                $text,
                $this->urlGenerator->generate('team'),
                $keywords,
                false,
                $member->getPhoto()
            );
        }

        foreach ($documents as $document) {
            $this->entityManager->persist($document);
        }

        $this->entityManager->flush();

        return [
            'indexed' => count($documents),
            'ignored' => $ignored,
            'references_without_safe_summary' => $referencesWithoutSafeSummary,
        ];
    }

    /**
     * @return ChatPublicDocument[]
     */
    public function buildDocumentSnapshot(): array
    {
        $snapshot = [];

        foreach ($this->sitePageRepository->findAll() as $page) {
            $status = $page->getPublicationStatus();
            if ($status !== null && $status !== 'published') {
                continue;
            }

            $text = trim($this->plain($page->getTitle()).' '.$this->plain($page->getMetaDescription()).' '.$this->plain($page->getHeroTitle()).' '.$this->plain($page->getHeroIntro()).' '.$this->plain($page->getBodyHtml()));
            if ($text === '') {
                continue;
            }

            $snapshot[] = $this->newDocument(
                'page',
                $page->getId(),
                (string) $page->getTitle(),
                $text,
                $this->resolvePageUrl((string) $page->getSlug()),
                array_merge($page->getCategories(), $page->getTags()),
                false,
                $page->getHeroImage()
            );
        }

        foreach ($this->practiceRepository->findAll() as $practice) {
            $text = trim($this->plain($practice->getDesignation()).' '.$this->plain($practice->getIntroductionShort()).' '.$this->plain($practice->getDescription()).' '.$this->plain($practice->getDescriptionShort()));
            if ($text === '') {
                continue;
            }

            $keywords = array_merge($practice->getTags() ?? [], [$practice->getDesignation(), $practice->getDesignationShort(), $practice->getH1Title()]);
            $snapshot[] = $this->newDocument(
                'expertise',
                $practice->getId(),
                (string) $practice->getDesignation(),
                $text,
                $this->urlGenerator->generate('practice_home', ['slug' => $practice->getSlug()]),
                $keywords,
                false,
                $practice->getImage1() ?: $practice->getImage2()
            );
        }

        foreach ($this->servicesRepository->findAll() as $service) {
            if (!$service->getPractice()) {
                continue;
            }

            $parts = [
                $service->getDesignation(),
                $service->getDesignationShort(),
                $service->getIntroductionShort(),
                $service->getDescriptionShort(),
                $service->getDescription(),
                $service->getPractice()?->getDesignation(),
            ];
            foreach ($service->getTeams() as $team) {
                $parts[] = $team->getNoncomplet();
                $parts[] = $team->getTitre();
            }

            $text = trim($this->plain(implode(' ', array_filter($parts))));
            if ($text === '') {
                continue;
            }

            $keywords = [$service->getDesignation(), $service->getDesignationShort(), $service->getPractice()?->getDesignation()];
            foreach ($service->getTeams() as $team) {
                $keywords[] = $team->getNoncomplet();
            }

            $snapshot[] = $this->newDocument(
                'service',
                $service->getId(),
                (string) $service->getDesignation(),
                $text,
                $this->urlGenerator->generate('service', [
                    'practice' => $service->getPractice()->getSlug(),
                    'slug' => $service->getSlug(),
                ]),
                $keywords,
                false,
                $service->getImageHero() ?: $service->getImage1() ?: $service->getImage2()
            );
        }

        foreach ($this->projetRepository->findAll() as $project) {
            $safeSummary = $this->buildSafeProjectSummary($project);
            if ($safeSummary === null) {
                continue;
            }

            $snapshot[] = $this->newDocument(
                'reference',
                $project->getId(),
                $safeSummary['title'],
                $safeSummary['text'],
                $this->urlGenerator->generate('projets'),
                $safeSummary['keywords'],
                true,
                $project->getImageHero() ?: $project->getImage()
            );
        }

        foreach ($this->teamRepository->findAll() as $member) {
            $parts = [
                $member->getNoncomplet(),
                $member->getTitre(),
                $member->getShortcv(),
            ];
            foreach ($member->getServices() as $service) {
                $parts[] = $service->getDesignation();
                $parts[] = $service->getPractice()?->getDesignation();
            }

            $text = trim($this->plain(implode(' ', array_filter($parts))));
            if ($text === '') {
                continue;
            }

            $keywords = [$member->getNoncomplet(), $member->getTitre()];
            foreach ($member->getServices() as $service) {
                $keywords[] = $service->getDesignation();
            }

            $snapshot[] = $this->newDocument(
                'team',
                $member->getId(),
                trim((string) $member->getNoncomplet().' '.(string) $member->getTitre()),
                $text,
                $this->urlGenerator->generate('team'),
                $keywords,
                false,
                $member->getPhoto()
            );
        }

        return $snapshot;
    }

    private function newDocument(
        string $sourceType,
        ?int $sourceEntityId,
        string $safeTitle,
        string $safeText,
        string $url,
        array $keywords,
        bool $isConfidentialReference,
        ?string $image
    ): ChatPublicDocument {
        $document = (new ChatPublicDocument())
            ->setSourceType($sourceType)
            ->setSourceEntityId($sourceEntityId)
            ->setSafeTitle(trim($safeTitle))
            ->setSafeText(trim($safeText))
            ->setUrl($url)
            ->setKeywords($this->normalizeKeywords($keywords))
            ->setSearchText($this->buildSearchText($safeTitle, $safeText, $keywords))
            ->setIsActive(true)
            ->setIsConfidentialReference($isConfidentialReference)
            ->setChecksum(hash('sha256', $sourceType.'|'.$sourceEntityId.'|'.$safeTitle.'|'.$safeText.'|'.$url))
            ->setUpdatedAt(new \DateTimeImmutable());

        if ($image !== null && trim($image) !== '') {
            $document->setImage(trim($image));
        }

        return $document;
    }

    private function resolvePageUrl(string $slug): string
    {
        return match ($slug) {
            'contact' => $this->urlGenerator->generate('contact'),
            'a-propos' => $this->urlGenerator->generate('apropos'),
            'ressources' => $this->urlGenerator->generate('seo_resources_index'),
            default => str_starts_with($slug, 'ressource-')
                ? $this->urlGenerator->generate('seo_resource', ['slug' => substr($slug, 10)])
                : '/'.$slug,
        };
    }

    /**
     * @return array{title:string,text:string,keywords:list<string>}|null
     */
    private function buildSafeProjectSummary(object $project): ?array
    {
        $parts = [];
        $titleParts = [];
        $keywords = [];

        $metier = method_exists($project, 'getMetier') ? $project->getMetier() : null;
        $metierName = $metier && method_exists($metier, 'getDesignation') ? $metier->getDesignation() : null;
        if (is_string($metierName) && trim($metierName) !== '') {
            $titleParts[] = $metierName;
            $parts[] = 'Secteur '.$metierName;
            $keywords[] = $metierName;
        }

        $services = method_exists($project, 'getServices') ? $project->getServices() : [];
        $serviceNames = [];
        foreach ($services as $service) {
            $name = method_exists($service, 'getDesignation') ? $service->getDesignation() : null;
            if (is_string($name) && trim($name) !== '') {
                $serviceNames[] = trim($name);
                $keywords[] = trim($name);
            }
        }
        if ($serviceNames !== []) {
            $titleParts[] = $serviceNames[0];
            $parts[] = 'Missions liées à '.implode(', ', array_slice($serviceNames, 0, 3));
        }

        $softwareTags = method_exists($project, 'getSoftwareTags') ? $project->getSoftwareTags() : [];
        $softwareFamilies = method_exists($project, 'getSoftwareFamilies') ? $project->getSoftwareFamilies() : [];
        $software = array_values(array_filter(array_merge($softwareTags, $softwareFamilies), static fn ($value): bool => is_string($value) && trim($value) !== ''));
        if ($software !== []) {
            $parts[] = 'Progiciels ou domaines: '.implode(', ', array_slice($software, 0, 4));
            $keywords = array_merge($keywords, $software);
        }

        $territory = method_exists($project, 'getTerritory') ? $project->getTerritory() : null;
        if (is_string($territory) && trim($territory) !== '') {
            $parts[] = 'Contexte territorial: '.$territory;
            $keywords[] = $territory;
        }

        $relation = method_exists($project, 'getSoftwareRelation') ? $project->getSoftwareRelation() : null;
        if (is_string($relation) && trim($relation) !== '') {
            $parts[] = 'Nature d’intervention: '.$relation;
            $keywords[] = $relation;
        }

        $description = method_exists($project, 'getDescription') ? $project->getDescription() : null;
        $safeDescription = $this->sanitizeProjectText($this->plain(is_string($description) ? $description : null));
        if ($safeDescription !== '') {
            $parts[] = $safeDescription;
        }

        $teams = method_exists($project, 'getTeams') ? $project->getTeams() : [];
        $teamNames = [];
        foreach ($teams as $team) {
            $name = method_exists($team, 'getNoncomplet') ? $team->getNoncomplet() : null;
            if (is_string($name) && trim($name) !== '') {
                $teamNames[] = trim($name);
            }
        }
        if ($teamNames !== []) {
            $parts[] = 'Profils mobilisables: '.implode(', ', array_slice($teamNames, 0, 3));
        }

        if ($parts === []) {
            return null;
        }

        $title = 'Référence OLING';
        if ($titleParts !== []) {
            $title = 'Référence '.implode(' - ', array_slice(array_unique($titleParts), 0, 2));
        }

        return [
            'title' => $title,
            'text' => implode('. ', $parts).'.',
            'keywords' => $this->normalizeKeywords($keywords),
        ];
    }

    /**
     * @param array<int, mixed> $keywords
     * @return list<string>
     */
    private function normalizeKeywords(array $keywords): array
    {
        $flat = [];
        foreach ($keywords as $keyword) {
            if (!is_string($keyword)) {
                continue;
            }

            $value = trim($keyword);
            if ($value === '') {
                continue;
            }

            $flat[] = $value;
            $normalized = $this->normalize($value);
            foreach (self::SYNONYMS as $label => $synonyms) {
                if (str_contains($normalized, $this->normalize($label))) {
                    array_push($flat, ...$synonyms);
                }
            }
        }

        return array_values(array_unique($flat));
    }

    private function buildSearchText(string $title, string $text, array $keywords): string
    {
        return $this->normalize(trim($title.' '.$text.' '.implode(' ', $this->normalizeKeywords($keywords))));
    }

    private function plain(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? '');
    }

    private function normalize(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        $normalized = strtolower($normalized);

        return trim(preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized);
    }

    private function sanitizeProjectText(string $text): string
    {
        if ($text === '') {
            return '';
        }

        $text = preg_replace('/^\s*(client|groupe|societe|société|entreprise|organisation)\s+[^:.-]{2,80}\s*[:.-]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/\b(client|groupe|societe|société|entreprise|organisation)\s+[A-Z0-9][A-Za-z0-9&\'’\-\s]{2,80}\b/u', '$1 anonymisé', $text) ?? $text;
        $text = preg_replace('/\b[A-Z]{3,}(?:\s+[A-Z0-9]{2,}){0,4}\s*[-:]\s*/u', '', $text, 1) ?? $text;
        $text = trim(preg_replace('/\s+/', ' ', $text) ?? $text);

        if ($text === '') {
            return '';
        }

        return mb_strlen($text) > 420 ? rtrim(mb_substr($text, 0, 417)).'...' : $text;
    }
}
