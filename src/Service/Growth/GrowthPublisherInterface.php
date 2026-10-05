<?php

namespace App\Service\Growth;

use App\Entity\GrowthPublication;
use App\Enum\GrowthDestination;

interface GrowthPublisherInterface
{
    public function supports(GrowthDestination $destination): bool;
    public function publish(GrowthPublication $publication): void;
}
