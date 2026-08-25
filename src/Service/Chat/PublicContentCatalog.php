<?php

namespace App\Service\Chat;

use App\Entity\ChatPublicDocument;
use App\Repository\ChatPublicDocumentRepository;

class PublicContentCatalog
{
    private const TYPE_LABELS = [
        'page' => 'Page',
        'expertise' => 'Expertise',
        'service' => 'Service',
        'reference' => 'Référence OLING',
        'team' => 'Équipe',
    ];

    private const SYNONYMS = [
        'erp' => ['progiciel', 'sage x3', 'sage', 'sap', 's4hana', 'divalto', 'cegid'],
        'gmao' => ['maintenance', 'actifs', 'equipements', 'parc', 'interventions', 'stocks', 'ordres de travail'],
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
        private readonly ChatPublicDocumentRepository $documentRepository,
        private readonly ChatPublicContentIndexer $indexer,
    ) {
    }

    /**
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    public function findRelevantDocuments(string $query, ?string $sourcePath = null, int $limit = 4): array
    {
        $documents = $this->activeDocuments();
        if ($documents === []) {
            return [];
        }

        $tokens = $this->expandedTokens($query);
        $normalizedQuery = $this->normalize($query);
        $scored = [];

        foreach ($documents as $document) {
            $score = $this->scoreDocument($document, $tokens, $normalizedQuery, $sourcePath);
            if ($score <= 0) {
                continue;
            }

            $scored[] = [
                'document' => $document,
                'score' => $score,
            ];
        }

        usort($scored, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return array_map(
            fn (array $row): array => $this->serializeDocument($row['document']),
            array_slice($scored, 0, $limit)
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
        foreach ($this->activeDocuments() as $document) {
            $url = $document->getUrl();
            if (!isset($byUrl[$url])) {
                $byUrl[$url] = $this->serializeCard($document);
            }

            if ($document->getSourceType() === 'reference') {
                $byUrl[$url] = [
                    'title' => 'Voir nos réalisations',
                    'url' => $url,
                    'type' => 'reference',
                    'typeLabel' => self::TYPE_LABELS['reference'],
                    'image' => null,
                    'excerpt' => 'Références OLING anonymisées par secteur, mission et contexte.',
                ];
            }
        }

        $cards = [];
        foreach (array_values(array_unique($urls)) as $url) {
            if (isset($byUrl[$url])) {
                $cards[] = $byUrl[$url];
            }
        }

        return array_slice($cards, 0, 2);
    }

    /**
     * @return ChatPublicDocument[]
     */
    private function activeDocuments(): array
    {
        try {
            $documents = $this->documentRepository->findActiveDocuments();
            if ($documents !== []) {
                return $documents;
            }
        } catch (\Throwable) {
            return $this->fallbackSnapshot();
        }

        try {
            $this->indexer->rebuild();

            return $this->documentRepository->findActiveDocuments();
        } catch (\Throwable) {
            return $this->fallbackSnapshot();
        }
    }

    /**
     * @return ChatPublicDocument[]
     */
    private function fallbackSnapshot(): array
    {
        try {
            return $this->indexer->buildDocumentSnapshot();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * @param string[] $tokens
     */
    private function scoreDocument(ChatPublicDocument $document, array $tokens, string $normalizedQuery, ?string $sourcePath): int
    {
        $score = 0;
        $title = $this->normalize($document->getSafeTitle());
        $body = $this->normalize($document->getSafeText());
        $keywords = $this->normalize(implode(' ', $document->getKeywords()));
        $search = $document->getSearchText();
        $isExpertIntent = $this->isExpertIntent($normalizedQuery);
        $isReferenceIntent = $this->isReferenceIntent($normalizedQuery);
        $isProjectIntent = $this->isProjectIntent($normalizedQuery);

        if ($normalizedQuery !== '' && str_contains($title, $normalizedQuery)) {
            $score += 12;
        }

        foreach ($tokens as $token) {
            if (str_contains($keywords, $token)) {
                $score += 7;
            }
            if (str_contains($title, $token)) {
                $score += 6;
            }
            if (str_contains($body, $token) || str_contains($search, $token)) {
                $score += 3;
            }
        }

        if ($sourcePath !== null && $sourcePath !== '' && $document->getUrl() === $sourcePath) {
            $score += 2;
        }

        if ($document->getSourceType() === 'team' && $isExpertIntent) {
            $score += 6;
        } elseif ($document->getSourceType() === 'team') {
            $score -= 6;
        }

        if ($document->getSourceType() === 'reference' && $isReferenceIntent) {
            $score += 8;
        } elseif ($document->getSourceType() === 'reference' && $isProjectIntent) {
            $score += 2;
        }

        if (in_array($document->getSourceType(), ['service', 'expertise'], true) && $isProjectIntent) {
            $score += 5;
        }

        if ($document->getSourceType() === 'page' && $isProjectIntent) {
            $score += 2;
        }

        return $score;
    }

    /**
     * @return array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}
     */
    private function serializeDocument(ChatPublicDocument $document): array
    {
        return [
            'title' => $document->getSafeTitle(),
            'url' => $document->getUrl(),
            'text' => $document->getSafeText(),
            'type' => $document->getSourceType(),
            'image' => $document->getImage(),
            'excerpt' => $this->excerpt($document->getSafeText()),
        ];
    }

    /**
     * @return array{title:string,url:string,type:string,typeLabel:string,image:?string,excerpt:string}
     */
    private function serializeCard(ChatPublicDocument $document): array
    {
        return [
            'title' => $document->getSafeTitle(),
            'url' => $document->getUrl(),
            'type' => $document->getSourceType(),
            'typeLabel' => self::TYPE_LABELS[$document->getSourceType()] ?? 'Ressource',
            'image' => in_array($document->getSourceType(), ['reference', 'team'], true) ? null : $document->getImage(),
            'excerpt' => $this->excerpt($document->getSafeText()),
        ];
    }

    /**
     * @return string[]
     */
    private function expandedTokens(string $query): array
    {
        $normalized = $this->normalize($query);
        $tokens = preg_split('/[^a-z0-9]+/i', $normalized) ?: [];
        $tokens = array_values(array_filter($tokens, static fn (string $token): bool => strlen($token) >= 2));
        $expanded = $tokens;

        foreach (self::SYNONYMS as $label => $synonyms) {
            if (str_contains($normalized, $this->normalize($label))) {
                foreach ($synonyms as $synonym) {
                    $expanded[] = $this->normalize($synonym);
                }
            }
        }

        return array_values(array_unique(array_slice($expanded, 0, 24)));
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

    private function excerpt(string $value, int $limit = 140): string
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

    private function isExpertIntent(string $normalizedQuery): bool
    {
        return preg_match('/\b(qui|quel expert|quels experts|expert|consultant|equipe|profil)\b/', $normalizedQuery) === 1;
    }

    private function isReferenceIntent(string $normalizedQuery): bool
    {
        return preg_match('/\b(reference|references|realisation|realisations|experience|experiences|cas client)\b/', $normalizedQuery) === 1;
    }

    private function isProjectIntent(string $normalizedQuery): bool
    {
        return preg_match('/\b(projet|amoa|erp|progiciel|facturation|client|crm|si|organisation|outil|consultation|cadrage)\b/', $normalizedQuery) === 1;
    }
}
