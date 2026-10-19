<?php

namespace App\Dto;

final class PublicMediaView
{
    public function __construct(
        private readonly ?string $image,
        private readonly ?string $alt,
        private readonly ?string $caption,
        private readonly ?string $focalPoint,
        private readonly string $mode,
        private readonly string $source,
        private readonly ?string $sourceLabel = null,
        private readonly string $displayMode = 'auto',
        private readonly string $size = 'auto',
    ) {
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function getDisplayMode(): string
    {
        return $this->displayMode;
    }

    public function getSize(): string
    {
        return $this->size;
    }

    public function getAlt(): ?string
    {
        return $this->alt;
    }

    public function getCaption(): ?string
    {
        return $this->caption;
    }

    public function getFocalPoint(): ?string
    {
        return $this->focalPoint;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getSourceLabel(): ?string
    {
        return $this->sourceLabel;
    }
}
