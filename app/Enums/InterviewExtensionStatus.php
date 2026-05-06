<?php

namespace App\Enums;

enum InterviewExtensionStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case DENIED = 'DENIED';
    case CANCELLED = 'CANCELLED';

    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
