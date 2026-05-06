<?php

namespace App\Enums;

enum OfferLetterStatus: string {
    case GENERATED = 'generated';
    case SENT = 'sent';
    case ACCEPTED = 'accepted';
    case DECLINED = 'declined';
    case SUPERSEDED = 'superseded';
    case EXPIRED = 'expired';
}
