<?php

namespace App\Enums;

enum InterviewAssignmentRole: string
{
    case PANEL_LEAD = 'PANEL_LEAD';
    case INTERVIEWER = 'INTERVIEWER';
    case OBSERVER = 'OBSERVER';

    public static function values(): array
    {
        return array_map(fn (self $role): string => $role->value, self::cases());
    }

    public static function officialScorerValues(): array
    {
        return [self::PANEL_LEAD->value, self::INTERVIEWER->value];
    }
}
