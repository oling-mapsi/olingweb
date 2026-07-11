<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class GrowthNewsInput
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 190)]
    public string $externalId = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $title = '';

    #[Assert\NotBlank]
    #[Assert\Regex(pattern: '/^[a-z0-9]+(?:-[a-z0-9]+)*$/')]
    #[Assert\Length(max: 255)]
    public string $slug = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    public string $excerpt = '';

    #[Assert\NotBlank]
    public string $contentHtml = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $metaTitle = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 2000)]
    public string $metaDescription = '';

    #[Assert\Length(max: 255)]
    public ?string $canonicalUrl = null;

    #[Assert\Length(max: 255)]
    public ?string $featuredImage = null;

    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(max: 80),
    ])]
    public array $categories = [];

    #[Assert\All([
        new Assert\Type('string'),
        new Assert\Length(max: 80),
    ])]
    public array $tags = [];

    #[Assert\NotNull]
    public ?\DateTimeImmutable $publicationDate = null;

    #[Assert\NotBlank]
    #[Assert\Choice(choices: ['draft'])]
    public string $status = 'draft';

    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    public string $authorDisplayName = '';

    #[Assert\NotBlank]
    #[Assert\Length(max: 190)]
    public string $sourceCampaignId = '';

    public static function fromArray(array $payload): self
    {
        $input = new self();
        $input->externalId = self::stringValue($payload['external_id'] ?? null);
        $input->title = self::stringValue($payload['title'] ?? null);
        $input->slug = self::stringValue($payload['slug'] ?? null);
        $input->excerpt = self::stringValue($payload['excerpt'] ?? null);
        $input->contentHtml = self::stringValue($payload['content_html'] ?? null);
        $input->metaTitle = self::stringValue($payload['meta_title'] ?? null);
        $input->metaDescription = self::stringValue($payload['meta_description'] ?? null);
        $input->canonicalUrl = self::nullableStringValue($payload['canonical_url'] ?? null);
        $input->featuredImage = self::nullableStringValue($payload['featured_image'] ?? null);
        $input->categories = self::stringList($payload['categories'] ?? []);
        $input->tags = self::stringList($payload['tags'] ?? []);
        $input->publicationDate = self::dateValue($payload['publication_date'] ?? null);
        $input->status = self::stringValue($payload['status'] ?? 'draft');
        $input->authorDisplayName = self::stringValue($payload['author_display_name'] ?? null);
        $input->sourceCampaignId = self::stringValue($payload['source_campaign_id'] ?? null);

        return $input;
    }

    private static function stringValue(mixed $value): string
    {
        return trim(is_scalar($value) ? (string) $value : '');
    }

    private static function nullableStringValue(mixed $value): ?string
    {
        $normalized = self::stringValue($value);

        return $normalized !== '' ? $normalized : null;
    }

    private static function stringList(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }

        $normalized = [];
        foreach ($value as $item) {
            $string = trim(is_scalar($item) ? (string) $item : '');
            if ($string !== '') {
                $normalized[] = $string;
            }
        }

        return array_values(array_unique($normalized));
    }

    private static function dateValue(mixed $value): ?\DateTimeImmutable
    {
        if (!is_scalar($value) || trim((string) $value) === '') {
            return null;
        }

        try {
            return new \DateTimeImmutable((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }
}
