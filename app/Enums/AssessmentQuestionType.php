<?php

namespace App\Enums;

enum AssessmentQuestionType: string
{
    case MCQ = 'MCQ';
    case THEORY = 'Theory';
    case CODING_TEXT = 'Coding Text';
}
