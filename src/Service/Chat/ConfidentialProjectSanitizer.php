<?php

namespace App\Service\Chat;

final class ConfidentialProjectSanitizer
{
    public const SAFE_INDEX_MARKER = 'oling-public-reference-v2';

    private const GENERIC_ORGANIZATION_WORDS = [
        'association', 'centre', 'certification', 'comite', 'competences', 'conseil',
        'economique', 'entreprise', 'formation', 'grand', 'groupe', 'institut',
        'maritime', 'mutuelle', 'organisation', 'port', 'regional', 'regionale',
        'sa', 'sarl', 'sas', 'service', 'services', 'social', 'societe', 'ville',
    ];

    /**
     * @param array<string, mixed> $metadata
     * @return list<string>
     */
    public function collectSensitiveIdentifiers(?string $organizationName, array $metadata = []): array
    {
        $identifiers = [];
        if (!$this->isGenericOrganizationLabel((string) $organizationName)) {
            $this->appendIdentifierVariants($identifiers, (string) $organizationName);
        }
        $this->appendMetadataIdentifiers($identifiers, $metadata);

        $identifiers = array_values(array_unique(array_filter(
            array_map('trim', $identifiers),
            static fn (string $identifier): bool => mb_strlen($identifier) >= 3
        )));
        usort($identifiers, static fn (string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));

        return $identifiers;
    }

    /**
     * @param string[] $sensitiveIdentifiers
     */
    public function sanitize(string $text, array $sensitiveIdentifiers): string
    {
        if ($text === '') {
            return '';
        }

        usort($sensitiveIdentifiers, static fn (string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));
        foreach (array_unique($sensitiveIdentifiers) as $identifier) {
            $identifier = trim($identifier);
            if (mb_strlen($identifier) < 3) {
                continue;
            }

            $text = preg_replace(
                '/(?<![\p{L}\p{N}])'.preg_quote($identifier, '/').'(?![\p{L}\p{N}])/iu',
                'organisation anonymisée',
                $text
            ) ?? $text;
        }

        $text = preg_replace('/^\s*pour\s+[^,.:;-]{2,100}\s*[,.:;-]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/^\s*(client|groupe|societe|société|entreprise|organisation)\s+[^:.-]{2,100}\s*[:.-]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/\b(client|groupe|societe|société|entreprise|organisation)\s+[A-Z0-9][A-Za-z0-9&\'’\-\s]{2,80}\b/u', '$1 anonymisé', $text) ?? $text;
        $text = preg_replace('/\b[A-Z]{3,}(?:\s+[A-Z0-9]{2,}){0,4}\s*[-:]\s*/u', '', $text, 1) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
    }

    /** @param list<string> $identifiers */
    private function appendIdentifierVariants(array &$identifiers, string $value): void
    {
        $value = trim($value);
        if ($value === '') {
            return;
        }

        $identifiers[] = $value;

        if (preg_match_all('/(?<![\p{L}\p{N}])[\p{Lu}\d][\p{Lu}\d&-]{2,}(?![\p{L}\p{N}])/u', $value, $matches)) {
            foreach ($matches[0] as $token) {
                if (!in_array($this->normalizeWord($token), self::GENERIC_ORGANIZATION_WORDS, true)) {
                    $identifiers[] = $token;
                }
            }
        }

        $words = preg_split('/[^\p{L}\p{N}]+/u', $value, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (count($words) < 2) {
            return;
        }

        foreach ([['de', 'du', 'des', 'd', 'et', 'via'], ['de', 'du', 'des', 'd', 'et', 'via', 'la', 'le', 'les']] as $stopWords) {
            $initials = '';
            foreach ($words as $word) {
                if (in_array(mb_strtolower($word), $stopWords, true)) {
                    continue;
                }
                $initials .= mb_substr($word, 0, 1);
            }
            if (mb_strlen($initials) >= 3) {
                $identifiers[] = mb_strtoupper($initials);
            }
        }
    }

    /**
     * @param list<string> $identifiers
     * @param array<string, mixed> $metadata
     */
    private function appendMetadataIdentifiers(array &$identifiers, array $metadata): void
    {
        foreach ($metadata as $key => $value) {
            if (!preg_match('/(?:^|_)(?:client|organisation|organization|raison_sociale|aliases?|acronym(?:e)?s?|short_name|nom_court)(?:_|$)/i', (string) $key)) {
                continue;
            }

            foreach ($this->flattenStrings($value) as $identifier) {
                $this->appendIdentifierVariants($identifiers, $identifier);
            }
        }
    }

    /** @return list<string> */
    private function flattenStrings(mixed $value): array
    {
        if (is_string($value)) {
            return [$value];
        }
        if (!is_array($value)) {
            return [];
        }

        $strings = [];
        array_walk_recursive($value, static function (mixed $item) use (&$strings): void {
            if (is_string($item)) {
                $strings[] = $item;
            }
        });

        return $strings;
    }

    private function normalizeWord(string $value): string
    {
        $normalized = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return mb_strtolower($normalized === false ? $value : $normalized);
    }

    private function isGenericOrganizationLabel(string $value): bool
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', trim($value), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if ($words === []) {
            return true;
        }

        $genericWords = [...self::GENERIC_ORGANIZATION_WORDS, 'de', 'du', 'des', 'd', 'et', 'la', 'le', 'les'];

        return array_reduce(
            $words,
            fn (bool $generic, string $word): bool => $generic && in_array($this->normalizeWord($word), $genericWords, true),
            true
        );
    }

    /**
     * Old index records are never sent to a model before a safe rebuild.
     *
     * @param string[] $keywords
     */
    public function safeIndexedText(string $text, array $keywords): string
    {
        if (in_array(self::SAFE_INDEX_MARKER, $keywords, true)) {
            return $text;
        }

        return 'Référence OLING anonymisée. Le contexte détaillé est disponible sur la page publique des projets.';
    }
}
