<?php

namespace App\Enums;

enum AssessmentQuestionType: string
{
    case MCQ = 'MCQ';
    case CODING = 'CODING';
    case THEORY = 'THEORY';
    case OTHER = 'OTHER';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
