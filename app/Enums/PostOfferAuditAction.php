<?php

namespace App\Enums;

enum PostOfferAuditAction: string
{
    case FINAL_EVALUATION_SAVE = 'FINAL_EVALUATION_SAVE';
    case OFFER_CREATE = 'OFFER_CREATE';
    case OFFER_SEND = 'OFFER_SEND';
    case OFFER_REPLACE = 'OFFER_REPLACE';
    case OFFER_ACCEPT = 'OFFER_ACCEPT';
    case OFFER_REJECT = 'OFFER_REJECT';
    case OFFER_EXPIRE = 'OFFER_EXPIRE';
    case ONBOARDING_CREATE = 'ONBOARDING_CREATE';
    case ONBOARDING_UPDATE = 'ONBOARDING_UPDATE';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
