<?php

namespace App\Enums;

enum AssessmentType: string
{
    case TECHNICAL = 'Technical';
    case APTITUDE = 'Aptitude';
    case CODING = 'Coding';
    case THEORY = 'Theory';
    case OTHER = 'Other';
}
