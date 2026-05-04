<?php

namespace App\Enums;

enum OfferType: string
{
    case FULL_TIME = 'FULL_TIME';
    case CONTRACT = 'CONTRACT';
    case INTERN = 'INTERN';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
