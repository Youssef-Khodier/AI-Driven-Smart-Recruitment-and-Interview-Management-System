<?php

namespace App\Enums;

enum ReferralRewardStatus: string {
    case NOT_APPLICABLE = 'not_applicable';
    case PENDING_REVIEW = 'pending_review';
    case ELIGIBLE = 'eligible';
    case REJECTED = 'rejected';
    case ON_HOLD = 'on_hold';
}
