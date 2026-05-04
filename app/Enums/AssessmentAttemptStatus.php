<?php

namespace App\Enums;

enum AssessmentAttemptStatus: string
{
    case IN_PROGRESS = 'IN_PROGRESS';
    case SUBMITTED = 'SUBMITTED';
    case EXPIRED = 'EXPIRED';

    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
