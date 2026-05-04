<?php

namespace App\Enums;

enum OnboardingStatus: string
{
    case PENDING = 'PENDING';
    case IN_PROGRESS = 'IN_PROGRESS';
    case COMPLETED = 'COMPLETED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
