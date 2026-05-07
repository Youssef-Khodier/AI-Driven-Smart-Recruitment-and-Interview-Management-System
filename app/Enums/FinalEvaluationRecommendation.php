<?php

namespace App\Enums;

enum FinalEvaluationRecommendation: string
{
    case STRONG_HIRE = 'STRONG_HIRE';
    case HIRE = 'HIRE';
    case HOLD = 'HOLD';
    case NO_HIRE = 'NO_HIRE';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
