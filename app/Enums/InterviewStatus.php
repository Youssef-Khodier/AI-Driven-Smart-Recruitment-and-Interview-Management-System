<?php

namespace App\Enums;

enum InterviewStatus: string
{
    case SCHEDULED = 'SCHEDULED';
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';

    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
