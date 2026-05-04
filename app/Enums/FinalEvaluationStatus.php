<?php

namespace App\Enums;

enum FinalEvaluationStatus: string
{
    case EVALUATED = 'EVALUATED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
