<?php

namespace App\Enums;

enum OfferStatus: string
{
    case DRAFT = 'DRAFT';
    case SENT = 'SENT';
    case ACCEPTED = 'ACCEPTED';
    case REJECTED = 'REJECTED';
    case EXPIRED = 'EXPIRED';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
