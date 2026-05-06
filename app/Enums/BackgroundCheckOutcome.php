<?php

namespace App\Enums;

enum BackgroundCheckOutcome: string {
    case CLEARED = 'cleared';
    case REVIEW_REQUIRED = 'review_required';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';
}
