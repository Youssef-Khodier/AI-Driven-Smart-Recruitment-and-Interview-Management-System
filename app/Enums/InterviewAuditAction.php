<?php

namespace App\Enums;

enum InterviewAuditAction: string
{
    case SCHEDULED = 'SCHEDULED';
    case RESCHEDULED = 'RESCHEDULED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
    case FEEDBACK_SUBMITTED = 'FEEDBACK_SUBMITTED';

    public static function values(): array
    {
        return array_map(fn (self $action): string => $action->value, self::cases());
    }
}
