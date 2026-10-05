<?php

namespace App\Enum;

enum GrowthDestination: string
{
    case OLING_PUBLIC = 'OLING_PUBLIC';
    case MAPSI_PUBLIC = 'MAPSI_PUBLIC';

    public function label(): string
    {
        return match ($this) {
            self::OLING_PUBLIC => 'OLING.fr',
            self::MAPSI_PUBLIC => 'mapsi.fr',
        };
    }
}
