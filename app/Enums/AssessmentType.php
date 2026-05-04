<?php

namespace App\Enums;

enum AssessmentType: string
{
    case TECHNICAL = 'TECHNICAL';
    case APTITUDE = 'APTITUDE';
    case CODING = 'CODING';
    case THEORY = 'THEORY';
    case OTHER = 'OTHER';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
