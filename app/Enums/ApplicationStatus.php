<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'Applied';
    case SCREENING = 'Screening';
    case ASSESSMENT = 'Assessment';
    case INTERVIEW = 'Interview';
    case OFFER = 'Offer';
    case REJECTED = 'Rejected';
    case HIRED = 'Hired';
}
