<?php

namespace App\Enums;

enum EvaluationDebriefStatus: string
{
    case PENDING = 'PENDING';
    case BLOCKED_BY_FLAG = 'BLOCKED_BY_FLAG';
    case COMPLETED = 'COMPLETED';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
