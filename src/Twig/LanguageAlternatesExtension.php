<?php

namespace App\Twig;

use App\Entity\SitePage;
use App\Repository\SitePageRepository;
use App\Service\I18n\LocalizedUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LanguageAlternatesExtension extends AbstractExtension
{
    public function __construct(
        private readonly SitePageRepository $sitePageRepository,
        private readonly LocalizedUrlGenerator $localizedUrlGenerator,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('oling_language_alternates', $this->languageAlternates(...)),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function languageAlternates(Request $request): array
    {
        $path = $request->getPathInfo();
        foreach ($this->sitePageRepository->findAll() as $page) {
            if (!$page instanceof SitePage) {
                continue;
            }

            foreach (['fr', 'en', 'es'] as $locale) {
                if ($this->localizedUrlGenerator->sitePagePath($page, $locale) === $path) {
                    return $this->localizedUrlGenerator->sitePageAlternates($page);
                }
            }
        }

        return [];
    }
}
