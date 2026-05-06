<?php

namespace App\Enums;

enum ArchiveActionStatus: string
{
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case BLOCKED = 'BLOCKED';
    case ARCHIVED = 'ARCHIVED';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
