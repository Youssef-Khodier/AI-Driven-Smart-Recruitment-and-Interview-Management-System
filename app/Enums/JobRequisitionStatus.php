<?php

namespace App\Enums;

enum JobRequisitionStatus: string
{
    case DRAFT = 'DRAFT';
    case PENDING = 'PENDING';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case OPEN = 'OPEN';
    case CLOSED = 'CLOSED';

    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
