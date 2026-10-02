<?php

namespace App\Service\ErpQuestionnaire;

class ErpQuestionnaireContentProvider
{
    public const LOCALE = 'fr';
    public const VERSION = 'v1';

    /** @var array<string, mixed>|null */
    private ?array $definition = null;

    /** @var array<string, array<string, mixed>> */
    private array $content = [];

    public function __construct(private readonly string $projectDir)
    {
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return $this->definition ??= $this->readJson($this->projectDir.'/data/i18n/erp_questionnaire.definition.json');
    }

    /**
     * @return array<string, mixed>
     */
    public function content(string $locale = self::LOCALE): array
    {
        return $this->content[$locale] ??= $this->readJson($this->projectDir.'/data/i18n/erp_questionnaire.'.$locale.'.json');
    }

    public function text(string $path, string $locale = self::LOCALE): string
    {
        $value = $this->path($this->content($locale), $path);

        return is_string($value) ? $value : '';
    }

    /**
     * @return array<string, string>
     */
    public function options(string $group, string $locale = self::LOCALE): array
    {
        $values = $this->path($this->content($locale), 'options.'.$group);

        return is_array($values) ? $values : [];
    }

    public function label(string $value, string $locale = self::LOCALE): string
    {
        foreach (['common', 'solutionType', 'functionalScope', 'amoaExpectations'] as $group) {
            $label = $this->options($group, $locale)[$value] ?? null;
            if (is_string($label)) {
                return $label;
            }
        }

        return trim($value);
    }

    public function fieldLabel(string $field, string $locale = self::LOCALE): string
    {
        $fields = $this->path($this->content($locale), 'fields');

        return is_array($fields) && is_string($fields[$field] ?? null) ? $fields[$field] : $field;
    }

    /**
     * @return array<string, mixed>
     */
    private function readJson(string $path): array
    {
        $json = file_get_contents($path);
        if ($json === false) {
            throw new \RuntimeException(sprintf('ERP questionnaire content file not readable: %s', $path));
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
