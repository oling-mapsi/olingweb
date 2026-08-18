<?php

namespace App\Twig;

use App\Repository\AgencyRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AgencyExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private AgencyRepository $agencyRepository,
        private string $siteBaseUrl = 'https://oling.fr',
    )
    {
    }

    public function getGlobals(): array
    {
        return [
            'agencies' => $this->agencyRepository->findAllOrdered(),
            'site_base_url' => rtrim($this->siteBaseUrl, '/'),
        ];
    }
}
