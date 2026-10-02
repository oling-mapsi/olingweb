<?php

namespace App\Service\Chat;

class AiConsultantContentProvider
{
    public const LOCALE = 'fr';
    public const VERSION = 'v1';

    /** @var array<string, array<string, mixed>> */
    private array $content = [];

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function content(string $locale = self::LOCALE): array
    {
        return $this->content[$locale] ??= $this->readJson($this->projectDir.'/data/i18n/ai_consultant.'.$locale.'.json');
    }

    public function text(string $path, string $locale = self::LOCALE): string
    {
        $value = $this->path($this->content($locale), $path);

        return is_string($value) ? $value : '';
    }

    public function label(?string $value, string $locale = self::LOCALE): string
    {
        if ($value === null || $value === '') {
            return $this->text('labels.unqualified', $locale);
        }

        $labels = $this->path($this->content($locale), 'labels');

        return is_array($labels) && is_string($labels[$value] ?? null) ? $labels[$value] : $value;
    }

    public function prompt(string $code, string $locale = self::LOCALE): string
    {
        $prompts = $this->content($locale)['prompts'] ?? [];
        $value = is_array($prompts) ? ($prompts[$code]['system'] ?? null) : null;

        return is_string($value) ? $value : '';
    }

    /**
     * @param array<string, string> $variables
     */
    public function renderPrompt(string $code, array $variables, string $locale = self::LOCALE): string
    {
        $prompts = $this->content($locale)['prompts'] ?? [];
        $template = is_array($prompts) && is_string($prompts[$code]['template'] ?? null) ? $prompts[$code]['template'] : '';
        foreach ($variables as $key => $value) {
            $template = str_replace('{{ '.$key.' }}', $value, $template);
        }

        return $template;
    }

    /**
     * @return string[]
     */
    public function list(string $path, string $locale = self::LOCALE): array
    {
        $value = $this->path($this->content($locale), $path);

        return is_array($value) ? array_values(array_filter($value, 'is_string')) : [];
    }

    /**
     * @return array<string, string>
     */
    public function map(string $path, string $locale = self::LOCALE): array
    {
        $value = $this->path($this->content($locale), $path);

        return is_array($value) ? array_filter($value, 'is_string') : [];
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException(sprintf('AI consultant content file not readable: %s', $path));
        }
        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        return is_array($data) ? $data : [];
    }

    /**
     * @param array<string, mixed> $data
     */
    private function path(array $data, string $path): mixed
    {
        $value = $data;
        foreach (explode('.', $path) as $part) {
            if (!is_array($value) || !array_key_exists($part, $value)) {
                return null;
            }
            $value = $value[$part];
        }

        return $value;
    }
}
