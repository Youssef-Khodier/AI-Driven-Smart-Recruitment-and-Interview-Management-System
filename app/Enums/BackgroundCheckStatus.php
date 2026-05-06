<?php

namespace App\Enums;

enum BackgroundCheckStatus: string {
    case NOT_REQUESTED = 'not_requested';
    case REQUESTED = 'requested';
    case CLEARED = 'cleared';
    case REVIEW_REQUIRED = 'review_required';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
