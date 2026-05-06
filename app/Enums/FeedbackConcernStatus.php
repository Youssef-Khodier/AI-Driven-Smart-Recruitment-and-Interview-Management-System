<?php

namespace App\Enums;

enum FeedbackConcernStatus: string
{
    case OPEN = 'OPEN';
    case RESOLVED_RESUME = 'RESOLVED_RESUME';
    case RESOLVED_BLOCKED = 'RESOLVED_BLOCKED';
    case RESOLVED_NO_HIRE = 'RESOLVED_NO_HIRE';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
