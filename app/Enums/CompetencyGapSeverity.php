<?php

namespace App\Enums;

enum CompetencyGapSeverity: string
{
    case MEETING = 'MEETING';
    case MINOR_GAP = 'MINOR_GAP';
    case MAJOR_GAP = 'MAJOR_GAP';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
