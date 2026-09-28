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
        'progiciel' => ['erp', 'pgi', 'logiciel metier'],
        'amoa' => ['moa', 'assistance maitrise d ouvrage', 'assistance a maitrise d ouvrage'],
        'selection erp' => ['choix erp', 'consultation erp', 'appel d offres erp'],
        'remplacement erp' => ['migration erp', 'refonte erp'],
        'reprise de donnees' => ['data migration', 'migration de donnees'],
        'recette' => ['uat', 'tests utilisateurs', 'tests metier'],
        'gmao' => ['maintenance', 'actifs', 'equipements', 'parc', 'interventions', 'stocks', 'ordres de travail'],
        'crm' => ['relation client', 'ventes', 'commercial', 'salesforce'],
        'sirh' => ['rh', 'paie', 'gestion des temps', 'ressources humaines'],
        'si finance' => ['finance', 'comptabilite', 'budget', 'facturation', 'reporting'],
        'rfe' => ['reforme facturation electronique', 'facturation electronique'],
        'rgpd' => ['dpo', 'dpd', 'cnil', 'registre', 'dpia', 'aipd', 'donnees personnelles'],
        'cyber' => ['iso 27001', 'ssi', 'nis2', 'dora', 'securite'],
        'pca pra' => ['continuite', 'reprise', 'resilience', 'iso 22301'],
        'data bi' => ['power bi', 'reporting', 'analytique', 'decisionnel'],
        'cadrage' => ['note de cadrage', 'expression des besoins', 'cahier des charges', 'macro planning', 'gouvernance projet'],
        'livrables' => ['note de cadrage', 'roadmap', 'cahier des charges', 'grille de choix', 'strategie de recette', 'plan de migration'],
        'transport' => ['transports', 'port', 'ports', 'aeroport', 'aeroports', 'mobilite', 'collectivite'],
        'eau assainissement' => ['eau', 'assainissement', 'eaux', 'regie', 'facturation eau'],
        'medico social' => ['medico social', 'ehpad', 'sante', 'hopital', 'social'],
        'public' => ['collectivite', 'collectivites', 'administration', 'organisation regulee', 'service public'],
        'industrie' => ['industriel', 'industrie', 'pmi', 'usine', 'production'],
        'services' => ['services b2b', 'societe de services', 'prestations', 'relation client'],
    ];

    public function __construct(
        private readonly ChatPublicDocumentRepository $documentRepository,
        private readonly ChatPublicContentIndexer $indexer,
        private readonly SectorTaxonomy $sectorTaxonomy = new SectorTaxonomy(),
        ?ChatOwnerRouter $ownerRouter = null,
        ?ConfidentialProjectSanitizer $confidentialProjectSanitizer = null,
    ) {
        $this->ownerRouter = $ownerRouter ?? new ChatOwnerRouter();
        $this->confidentialProjectSanitizer = $confidentialProjectSanitizer ?? new ConfidentialProjectSanitizer();
    }

    private readonly ChatOwnerRouter $ownerRouter;
    private readonly ConfidentialProjectSanitizer $confidentialProjectSanitizer;

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

        $selected = array_slice($scored, 0, $limit);
        $selectedHasReference = array_filter($selected, static fn (array $row): bool => $row['document']->getSourceType() === 'reference') !== [];
        if ($this->isReferenceIntent($normalizedQuery) && !$selectedHasReference) {
            $reference = null;
            foreach ($scored as $row) {
                if ($row['document']->getSourceType() === 'reference') {
                    $reference = $row;
                    break;
                }
            }
            if ($reference !== null) {
                $selected[max(0, count($selected) - 1)] = $reference;
            }
        }

        return array_map(
            fn (array $row): array => $this->serializeDocument($row['document'], $row['score']),
            $selected
        );
    }

    public function detectSector(string $query): ?string
    {
        $managedSector = $this->detectManagedSector($query);
        if ($managedSector !== null) {
            return $managedSector;
        }

        return $this->sectorTaxonomy->detect($query);
    }

    /**
     * @return array<int, array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}>
     */
    public function findSectorReferences(string $sector, string $query = '', int $limit = 3): array
    {
        $documents = $this->activeDocuments();
        if ($documents === []) {
            return [];
        }

        $sectorNeedle = $this->normalize($sector);
        $aliases = array_map(fn (string $alias): string => $this->normalize($alias), $this->sectorTaxonomy->aliasesFor($sector));
        $tokens = $this->expandedTokens(trim($query.' '.$sector.' '.implode(' ', $aliases)));
        $scored = [];

        foreach ($documents as $document) {
            if ($document->getSourceType() !== 'reference') {
                continue;
            }

            $haystack = $this->normalize($document->getSafeTitle().' '.$document->getSafeText().' '.implode(' ', $document->getKeywords()).' '.$document->getSearchText());
            $sectorMatch = str_contains($haystack, $sectorNeedle);
            foreach ($aliases as $alias) {
                $sectorMatch = $sectorMatch || ($alias !== '' && str_contains($haystack, $alias));
            }
            if (!$sectorMatch) {
                continue;
            }

            $score = 80;
            foreach ($tokens as $token) {
                if (str_contains($haystack, $token)) {
                    $score += 4;
                }
            }

            $scored[] = ['document' => $document, 'score' => $score];
        }

        usort($scored, static fn (array $left, array $right): int => $right['score'] <=> $left['score']);

        return array_map(
            fn (array $row): array => $this->serializeDocument($row['document'], $row['score']),
            array_slice($scored, 0, $limit)
        );
    }

    private function detectManagedSector(string $query): ?string
    {
        $normalizedQuery = $this->normalize($query);
        if ($normalizedQuery === '') {
            return null;
        }

        foreach ($this->activeDocuments() as $document) {
            if ($document->getSourceType() !== 'reference') {
                continue;
            }

            $sector = $this->extractSectorFromDocument($document);
            if ($sector === null) {
                continue;
            }

            $normalizedSector = $this->normalize($sector);
            if ($normalizedSector !== '' && str_contains($normalizedQuery, $normalizedSector)) {
                return $sector;
            }
        }

        return null;
    }

    private function extractSectorFromDocument(ChatPublicDocument $document): ?string
    {
        $text = trim($document->getSafeText());
        if (preg_match('/\bSecteur\s+([^.;\n]+)/u', $text, $matches) !== 1) {
            return null;
        }

        $sector = trim($matches[1]);

        return $sector === '' ? null : $sector;
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
        $isSectorIntent = $this->isSectorIntent($normalizedQuery);
        $isMethodIntent = $this->isMethodIntent($normalizedQuery);
        $expectedOwner = $this->ownerRouter->resolveOwnerUrl($normalizedQuery);
        $detectedSector = $this->detectSector($normalizedQuery);

        if ($expectedOwner !== null && $document->getUrl() === $expectedOwner) {
            $score += 120;
        } elseif ($expectedOwner !== null && $document->getSourceType() === 'reference') {
            $score -= 8;
        }

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

        if ($document->getSourceType() === 'reference' && $isSectorIntent) {
            $score += 6;
        }

        if ($detectedSector !== null) {
            $sectorAliases = array_map(fn (string $alias): string => $this->normalize($alias), [$detectedSector, ...$this->sectorTaxonomy->aliasesFor($detectedSector)]);
            $haystack = $title.' '.$body.' '.$keywords.' '.$search;
            foreach ($sectorAliases as $alias) {
                if ($alias !== '' && str_contains($haystack, $alias)) {
                    $score += $document->getSourceType() === 'reference' ? 40 : 18;
                    break;
                }
            }
        }

        $referenceHaystack = $title.' '.$body.' '.$keywords.' '.$search;
        if (
            $document->getSourceType() === 'reference'
            && preg_match('/\bgrands? ports?\b/', $normalizedQuery) === 1
            && str_contains($referenceHaystack, 'grand port maritime')
        ) {
            $score += 100;
        }
        if (
            $document->getSourceType() === 'reference'
            && preg_match('/\b(continuite|resilience|pca|pra)\b/', $normalizedQuery) === 1
            && preg_match('/\b(mission de pca|plan de continuite|continuite d activite|plan de reprise)\b/', $referenceHaystack) === 1
        ) {
            $score += 60;
        }

        if (in_array($document->getSourceType(), ['service', 'expertise'], true) && $isProjectIntent) {
            $score += 5;
        }

        if ($document->getSourceType() === 'page' && $isProjectIntent) {
            $score += 2;
        }

        if ($document->getSourceType() === 'page' && $isSectorIntent) {
            $score += 5;
        }

        if (in_array($document->getSourceType(), ['service', 'expertise', 'page'], true) && $isMethodIntent) {
            $score += 5;
        }

        if ($isMethodIntent && preg_match('/\b(note de cadrage|expression des besoins|cahier des charges|gouvernance projet|strategie de recette|plan de migration|reprise de donnees|cartographie)\b/', $body.' '.$keywords) === 1) {
            $score += 6;
        }

        return $score;
    }

    /**
     * @return array{title:string,url:string,text:string,type:string,image:?string,excerpt:string}
     */
    private function serializeDocument(ChatPublicDocument $document, int $score): array
    {
        $text = $document->getSafeText();
        if ($document->isConfidentialReference()) {
            $text = $this->confidentialProjectSanitizer->safeIndexedText($text, $document->getKeywords());
        }

        return [
            'title' => $document->getSafeTitle(),
            'url' => $document->getUrl(),
            'text' => $text,
            'type' => $document->getSourceType(),
            'image' => $document->getImage(),
            'excerpt' => $this->excerpt($text),
            'score' => $score,
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
        $value = strtr($value, [
            'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ç' => 'c', 'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'ö' => 'o', 'ù' => 'u', 'û' => 'u', 'ü' => 'u', 'ÿ' => 'y',
            'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ç' => 'C', 'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'Î' => 'I', 'Ï' => 'I', 'Ô' => 'O', 'Ö' => 'O', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U', 'Ÿ' => 'Y',
        ]);
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
        return preg_match('/\b(reference|references|realisation|realisations|experience|experiences|cas client|accompagne|accompagnes|accompagnement|intervenu|travaille)\b/', $normalizedQuery) === 1;
    }

    private function isProjectIntent(string $normalizedQuery): bool
    {
        return preg_match('/\b(projet|amoa|erp|progiciel|facturation|client|crm|si|organisation|outil|consultation|cadrage)\b/', $normalizedQuery) === 1;
    }

    private function isSectorIntent(string $normalizedQuery): bool
    {
        return $this->sectorTaxonomy->detect($normalizedQuery) !== null
            || preg_match('/\b(secteur|public|services)\b/', $normalizedQuery) === 1;
    }

    private function isMethodIntent(string $normalizedQuery): bool
    {
        return preg_match('/\b(cadrage|livrable|livrables|expression des besoins|cahier des charges|recette|reprise|migration|gouvernance|macro planning|roadmap)\b/', $normalizedQuery) === 1;
    }
}
