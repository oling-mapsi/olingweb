<?php

namespace App\Enum;

enum GrowthCampaignStatus: string
{
    case DRAFT = 'draft';
    case IN_REVIEW = 'in_review';
    case APPROVED = 'approved';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';
}
