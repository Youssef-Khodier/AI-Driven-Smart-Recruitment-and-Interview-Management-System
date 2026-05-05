<?php

namespace App\Enums;

enum RetentionAuditAction: string
{
    case CANDIDATE_ANONYMIZED = 'CANDIDATE_ANONYMIZED';
    case CANDIDATE_DELETED = 'CANDIDATE_DELETED';

    public static function values(): array
    {
        return array_map(fn (self $action): string => $action->value, self::cases());
    }
}