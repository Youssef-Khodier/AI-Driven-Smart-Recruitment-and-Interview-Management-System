<?php

namespace App\Enums;

enum ComplianceRunCheckType: string
{
    case MISSING_FEEDBACK = 'MISSING_FEEDBACK';
    case OFFER_EXPIRY = 'OFFER_EXPIRY';
    case BACKGROUND_CHECK_DELAY = 'BACKGROUND_CHECK_DELAY';
    case ONBOARDING_OVERDUE = 'ONBOARDING_OVERDUE';
    case ARCHIVE_CLOSED_REQUISITIONS = 'ARCHIVE_CLOSED_REQUISITIONS';
    case ARCHIVE_REJECTED_CANDIDATES = 'ARCHIVE_REJECTED_CANDIDATES';
    case ALL_CHECKS = 'ALL_CHECKS';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
