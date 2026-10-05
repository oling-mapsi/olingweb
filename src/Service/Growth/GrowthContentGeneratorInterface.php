<?php

namespace App\Service\Growth;

use App\Entity\GrowthCampaign;
use App\Entity\GrowthContent;

interface GrowthContentGeneratorInterface
{
    public function generate(GrowthCampaign $campaign): GrowthContent;
}
