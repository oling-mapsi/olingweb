<?php

namespace App\Service\I18n;

interface AiTranslationProviderInterface
{
    /**
     * @param array<string, mixed> $sourcePayload
     * @param array<string, mixed> $glossary
     */
    public function translate(array $sourcePayload, string $targetLocale, array $glossary): AiTranslationResult;
}
