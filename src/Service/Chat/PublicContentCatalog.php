<?php

namespace App\Service\Chat;

use App\Repository\PracticeRepository;
use App\Repository\ProjetRepository;
use App\Repository\ServicesRepository;
use App\Repository\SitePageRepository;
use App\Repository\TeamRepository;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PublicContentCatalog
{
    private const TYPE_LABELS = [
        'page' => 'Page',
        'expertise' => 'Expertise',
        'service' => 'Service',
        'cas_client' => 'Cas client',
        'equipe' => 'Équipe',
    ];

    public function __construct(
        private readonly SitePageRepository $sitePageRepository,
        private readonly PracticeRepository $practiceRepository,
        private readonly ServicesRepository $servicesRepository,
        private readonly ProjetRepository $projetRepository,
        private readonly TeamRepository $teamRepository,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    public function findRelevantDocuments(string $query, ?string $sourcePath = null, int $limit = 3): array
    {
        $tokens = $this->tokenize($query);
        if ($tokens === []) {
            return [];
        }

        $documents = [];
        foreach ($this->buildDocuments() as $document) {
            $haystack = $this->normalize($document['title'].' '.$document['text'].' '.$document['type']);
            $score = 0;
            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score += 4;
                }
                if (str_contains($this->normalize($document['title']), $token)) {
                    $score += 3;
                }
            }

            if ($sourcePath && $document['url'] === $sourcePath) {
                $score += 2;
            }

            if ($score <= 0) {
                continue;
            }

            $document['score'] = $score;
            $documents[] = $document;
        }

        usort($documents, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);

        return array_map(
            static fn (array $document): array => [
                'title' => $document['title'],
                'url' => $document['url'],
                'text' => $document['text'],
                'type' => $document['type'],
                'image' => $document['image'],
                'excerpt' => $document['excerpt'],
            ],
            array_slice($documents, 0, $limit)
        );
    }

    /**
     * @param string[] $urls
     * @return array<int, array{title:string,url:string,type:string,typeLabel:string,image:?string,excerpt:string}>
     */
    public function findCardsByUrls(array $urls): array
    {
        if ($urls === []) {
            return [];
        }

        $byUrl = [];
        foreach ($this->buildDocuments() as $document) {
            $byUrl[$document['url']] = [
                'title' => $document['title'],
                'url' => $document['url'],
                'type' => $document['type'],
                'typeLabel' => self::TYPE_LABELS[$document['type']] ?? ucfirst(str_replace('_', ' ', $document['type'])),
                'image' => $document['image'],
                'excerpt' => $document['excerpt'],
            ];
        }

        $cards = [];
        foreach ($urls as $url) {
            if (isset($byUrl[$url])) {
                $cards[] = $byUrl[$url];
            }
        }

        return $cards;
    }

    /**
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    private function buildDocuments(): array
    {
        $documents = [];

        foreach ($this->sitePageRepository->findAll() as $page) {
            $status = $page->getPublicationStatus();
            if ($status !== null && $status !== 'published') {
                continue;
            }

            $url = '/'.$page->getSlug();
            if ($page->getSlug() === 'contact') {
                $url = $this->urlGenerator->generate('contact');
            } elseif ($page->getSlug() === 'a-propos') {
                $url = $this->urlGenerator->generate('apropos');
            } elseif ($page->getSlug() === 'ressources') {
                $url = $this->urlGenerator->generate('seo_resources_index');
            } elseif (str_starts_with((string) $page->getSlug(), 'ressource-')) {
                $url = $this->urlGenerator->generate('seo_resource', ['slug' => substr((string) $page->getSlug(), 10)]);
            }

            $text = trim($this->plain($page->getHeroTitle()).' '.$this->plain($page->getHeroIntro()).' '.$this->plain($page->getBodyHtml()));
            if ($text === '') {
                continue;
            }

            $documents[] = [
                'title' => (string) $page->getTitle(),
                'url' => $url,
                'text' => $text,
                'type' => 'page',
                'image' => $this->normalizeImagePath($page->getHeroImage()),
                'excerpt' => $this->excerpt($text),
            ];
        }

        foreach ($this->practiceRepository->findAll() as $practice) {
            $text = trim($this->plain($practice->getIntroductionShort()).' '.$this->plain($practice->getDescription()));
            $documents[] = [
                'title' => (string) $practice->getDesignation(),
                'url' => $this->urlGenerator->generate('practice_home', ['slug' => $practice->getSlug()]),
                'text' => $text,
                'type' => 'expertise',
                'image' => $this->normalizeImagePath($practice->getImage1() ?: $practice->getImage2()),
                'excerpt' => $this->excerpt($text),
            ];
        }

        foreach ($this->servicesRepository->findAll() as $service) {
            if (!$service->getPractice()) {
                continue;
            }

            $text = trim($this->plain($service->getIntroductionShort()).' '.$this->plain($service->getDescription()));
            $documents[] = [
                'title' => (string) $service->getDesignation(),
                'url' => $this->urlGenerator->generate('service', [
                    'practice' => $service->getPractice()->getSlug(),
                    'slug' => $service->getSlug(),
                ]),
                'text' => $text,
                'type' => 'service',
                'image' => $this->normalizeImagePath($service->getImageHero() ?: $service->getImage1() ?: $service->getImage2()),
                'excerpt' => $this->excerpt($text),
            ];
        }

        foreach ($this->projetRepository->findAll() as $project) {
            $text = trim($this->plain($project->getDescription()));
            $documents[] = [
                'title' => (string) $project->getDesignation(),
                'url' => $this->urlGenerator->generate('projets'),
                'text' => $text,
                'type' => 'cas_client',
                'image' => $this->normalizeImagePath($project->getImageHero() ?: $project->getImage()),
                'excerpt' => $this->excerpt($text),
            ];
        }

        foreach ($this->teamRepository->findAll() as $member) {
            $text = trim($this->plain($member->getShortcv()));
            $documents[] = [
                'title' => trim((string) $member->getNoncomplet().' '.(string) $member->getTitre()),
                'url' => $this->urlGenerator->generate('team'),
                'text' => $text,
                'type' => 'equipe',
                'image' => $this->normalizeImagePath($member->getPhoto()),
                'excerpt' => $this->excerpt($text),
            ];
        }

        return array_filter($documents, static fn (array $document): bool => trim($document['text']) !== '');
    }

    private function plain(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', strip_tags((string) $value)) ?? '');
    }

    /**
     * @return string[]
     */
    private function tokenize(string $value): array
    {
        $tokens = preg_split('/[^a-z0-9]+/i', $this->normalize($value)) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => strlen($token) >= 3));

        return array_slice(array_unique($tokens), 0, 12);
    }

    private function normalize(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
        if ($normalized === false) {
            $normalized = $value;
        }

        return strtolower($normalized);
    }

    private function excerpt(string $value, int $limit = 120): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        if (mb_strlen($value) <= $limit) {
            return $value;
        }

        return rtrim(mb_substr($value, 0, $limit - 1)).'…';
    }

    private function normalizeImagePath(?string $path): ?string
    {
        $path = trim((string) $path);

        return $path !== '' ? $path : null;
    }
}
