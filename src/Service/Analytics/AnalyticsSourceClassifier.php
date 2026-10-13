<?php

namespace App\Service\Analytics;

class AnalyticsSourceClassifier
{
    public function classify(?string $utmSource, ?string $referrer): string
    {
        $source = $this->classifyValue($utmSource);
        if ($source) {
            return $source;
        }

        $source = $this->classifyValue($referrer);
        if ($source) {
            return $source;
        }

        return $referrer ? 'other_referral' : 'direct';
    }

    private function classifyValue(?string $value): ?string
    {
        $normalized = mb_strtolower((string) $value);
        if ($normalized === '') {
            return null;
        }

        return match (true) {
            str_contains($normalized, 'chatgpt') || str_contains($normalized, 'openai') => 'chatgpt_openai',
            str_contains($normalized, 'claude') || str_contains($normalized, 'anthropic') => 'claude_anthropic',
            str_contains($normalized, 'perplexity') => 'perplexity',
            str_contains($normalized, 'linkedin') || str_contains($normalized, 'lnkd.in') => 'linkedin',
            str_contains($normalized, 'bing') || str_contains($normalized, 'copilot') => 'bing',
            str_contains($normalized, 'google') => 'google',
            str_contains($normalized, 'facebook') || str_contains($normalized, 'instagram') || str_contains($normalized, 'twitter') || str_contains($normalized, 'x.com') => 'social_other',
            default => null,
        };
    }
}
