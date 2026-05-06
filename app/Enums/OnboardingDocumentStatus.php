<?php

namespace App\Enums;

enum OnboardingDocumentStatus: string {
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case NEEDS_CORRECTION = 'needs_correction';
    case NOT_REQUIRED = 'not_required';
}
