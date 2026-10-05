<?php

namespace App\Enum;

enum GrowthContentStatus: string
{
    case DRAFT = 'draft';
    case GENERATED = 'generated';
    case REVIEWED = 'reviewed';
    case APPROVED = 'approved';
}
