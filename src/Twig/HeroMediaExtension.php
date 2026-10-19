<?php

namespace App\Twig;

use App\Service\HeroMediaPresentationResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class HeroMediaExtension extends AbstractExtension
{
    public function __construct(private readonly HeroMediaPresentationResolver $resolver) {}

    public function getFunctions(): array
    {
        return [new TwigFunction('hero_media_presentation', [$this->resolver, 'resolve'])];
    }
}
