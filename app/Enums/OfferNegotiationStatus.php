<?php

namespace App\Enums;

enum OfferNegotiationStatus: string {
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case WITHDRAWN = 'withdrawn';
    case CLOSED = 'closed';
}
