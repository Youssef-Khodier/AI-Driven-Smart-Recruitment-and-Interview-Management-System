<?php

namespace App\Enums;

enum ApplicationStatus: string
{
    case APPLIED = 'APPLIED';
    case SCREENING = 'SCREENING';
    case ASSESSMENT = 'ASSESSMENT';
    case INTERVIEW = 'INTERVIEW';
    case OFFER = 'OFFER';
    case REJECTED = 'REJECTED';
    case HIRED = 'HIRED';

    public static function values(): array
    {
        return array_map(fn (self $status): string => $status->value, self::cases());
    }
}
