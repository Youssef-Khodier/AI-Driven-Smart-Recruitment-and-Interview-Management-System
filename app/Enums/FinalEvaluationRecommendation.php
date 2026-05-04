<?php

namespace App\Enums;

enum FinalEvaluationRecommendation: string
{
    case STRONG_HIRE = 'STRONG_HIRE';
    case HIRE = 'HIRE';
    case NO_HIRE = 'NO_HIRE';
    case STRONG_NO_HIRE = 'STRONG_NO_HIRE';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
