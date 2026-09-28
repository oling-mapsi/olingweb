<?php

namespace App\Service\Chat;

final class ConfidentialProjectSanitizer
{
    public const SAFE_INDEX_MARKER = 'oling-public-reference-v2';

    /**
     * @param string[] $organizationNames
     */
    public function sanitize(string $text, array $organizationNames): string
    {
        if ($text === '') {
            return '';
        }

        usort($organizationNames, static fn (string $left, string $right): int => mb_strlen($right) <=> mb_strlen($left));
        foreach (array_unique($organizationNames) as $organizationName) {
            $organizationName = trim($organizationName);
            if (mb_strlen($organizationName) < 3) {
                continue;
            }

            $text = preg_replace('/'.preg_quote($organizationName, '/').'/iu', 'organisation anonymisée', $text) ?? $text;
        }

        $text = preg_replace('/^\s*pour\s+[^,.:;-]{2,100}\s*[,.:;-]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/^\s*(client|groupe|societe|société|entreprise|organisation)\s+[^:.-]{2,100}\s*[:.-]\s*/iu', '', $text) ?? $text;
        $text = preg_replace('/\b(client|groupe|societe|société|entreprise|organisation)\s+[A-Z0-9][A-Za-z0-9&\'’\-\s]{2,80}\b/u', '$1 anonymisé', $text) ?? $text;
        $text = preg_replace('/\b[A-Z]{3,}(?:\s+[A-Z0-9]{2,}){0,4}\s*[-:]\s*/u', '', $text, 1) ?? $text;

        return trim(preg_replace('/\s+/', ' ', $text) ?? $text);
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
