<?php

namespace App\Enums;

enum NotificationType: string
{
    case STATUS_CHANGE = 'STATUS_CHANGE';
    case FEEDBACK_REMINDER = 'FEEDBACK_REMINDER';
    case OFFER_EXPIRING_SOON = 'OFFER_EXPIRING_SOON';
    case OFFER_EXPIRED = 'OFFER_EXPIRED';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}