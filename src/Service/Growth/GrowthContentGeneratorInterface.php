<?php

namespace App\Service\Growth;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;
use App\Enum\GrowthDestination;

interface GrowthContentGeneratorInterface
{
    public function generate(GrowthCampaign $campaign, ?GrowthDestination $destination = null): GrowthContent;
}
