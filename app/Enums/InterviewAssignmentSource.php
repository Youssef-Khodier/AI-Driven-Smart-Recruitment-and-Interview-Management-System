<?php

namespace App\Enums;

enum InterviewAssignmentSource: string
{
    case RECOMMENDED = 'RECOMMENDED';
    case MANUAL = 'MANUAL';
    case OVERRIDE = 'OVERRIDE';

    public static function values(): array
    {
        return array_map(fn (self $source): string => $source->value, self::cases());
    }
}
