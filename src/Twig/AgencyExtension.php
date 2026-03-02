<?php

namespace App\Twig;

use App\Repository\AgencyRepository;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AgencyExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private AgencyRepository $agencyRepository)
    {
    }

    public function getGlobals(): array
    {
        return [
            'agencies' => $this->agencyRepository->findAllOrdered(),
        ];
    }
}
