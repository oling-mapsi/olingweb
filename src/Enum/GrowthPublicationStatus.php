<?php

namespace App\Enum;

enum GrowthPublicationStatus: string
{
    case PENDING = 'pending';
    case PUBLISHED = 'published';
    case FAILED = 'failed';
    case DESIGN_ONLY = 'design_only';
}
