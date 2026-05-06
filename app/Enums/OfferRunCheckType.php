<?php

namespace App\Enums;

enum OfferRunCheckType: string {
    case EXPIRY = 'expiry';
    case BACKGROUND_CHECK = 'background_check';
}
