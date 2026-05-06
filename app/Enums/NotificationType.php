<?php

namespace App\Enums;

enum NotificationType: string
{
    case STATUS_CHANGE = 'STATUS_CHANGE';
    case FEEDBACK_REMINDER = 'FEEDBACK_REMINDER';
    case OFFER_EXPIRING_SOON = 'OFFER_EXPIRING_SOON';
    case OFFER_EXPIRED = 'OFFER_EXPIRED';
    case MISSING_FEEDBACK_ESCALATION = 'MISSING_FEEDBACK_ESCALATION';
    case OFFER_EXPIRY_ESCALATION = 'OFFER_EXPIRY_ESCALATION';
    case BACKGROUND_CHECK_ESCALATION = 'BACKGROUND_CHECK_ESCALATION';
    case ONBOARDING_OVERDUE_ESCALATION = 'ONBOARDING_OVERDUE_ESCALATION';
    case ARCHIVE_FOLLOWUP = 'ARCHIVE_FOLLOWUP';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}