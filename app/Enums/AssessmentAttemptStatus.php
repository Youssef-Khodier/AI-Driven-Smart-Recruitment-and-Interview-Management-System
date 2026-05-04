<?php

namespace App\Enums;

enum AssessmentAttemptStatus: string
{
    case IN_PROGRESS = 'In Progress';
    case SUBMITTED = 'Submitted';
    case EXPIRED = 'Expired';
}
