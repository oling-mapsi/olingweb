<?php

namespace App\Dto;

class StructuredContentAdminFormData
{
    public ?string $designation = null;
    public ?string $designationShort = null;
    public ?string $h1Title = null;
    public ?string $introduction = null;
    public ?string $introductionShort = null;
    public ?string $description = null;
    public ?string $descriptionShort = null;
    public array $tags = [];
    public bool $featuredHome = false;
    public ?string $class1 = null;
    public ?string $color = null;
    public ?string $ico = null;
    public ?string $image1 = null;
    public ?string $image2 = null;
    public ?string $imageHero = null;
    public mixed $practice = null;
    public mixed $teams = null;
    public mixed $services = null;
    public mixed $metier = null;
    public ?string $class = null;
    public bool $featuredProjects = false;
    public ?string $image = null;
    public ?string $clientName = null;
    public ?string $territory = null;
    public ?string $periodLabel = null;
    public ?string $publicUrl = null;
    public ?string $shortDescription = null;
    public ?string $noncomplet = null;
    public ?string $titre = null;
    public ?string $shortcv = null;
    public ?string $linkedin = null;
    public ?string $photo = null;
    public ?string $title = null;
    public ?string $body = null;
    public ?string $eyebrow = null;
    public ?string $intro = null;
    public ?string $ctaLabel = null;
    public ?string $ctaUrl = null;

    public function __call(string $name, array $arguments): mixed
    {
        if (str_starts_with($name, 'get')) {
            $property = lcfirst(substr($name, 3));
            return $this->{$property} ?? null;
        }

        if (str_starts_with($name, 'is')) {
            $property = lcfirst(substr($name, 2));
            return (bool) ($this->{$property} ?? false);
        }

        if (str_starts_with($name, 'set')) {
            $property = lcfirst(substr($name, 3));
            $this->{$property} = $arguments[0] ?? null;
            return $this;
        }

        throw new \BadMethodCallException(sprintf('Unknown method "%s".', $name));
    }

    public function getTags(): array
    {
        return $this->tags;
    }

    public function setTags(?array $tags): self
    {
        $this->tags = $tags ?? [];

        return $this;
    }
}
