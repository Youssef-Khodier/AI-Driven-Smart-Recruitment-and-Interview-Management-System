<?php

namespace App\Enums;

enum UserRole: string
{
    case HR_ADMIN = 'HR_ADMIN';
    case INTERVIEWER = 'INTERVIEWER';
    case CANDIDATE = 'CANDIDATE';
    case JUNIOR_STAFF = 'JUNIOR_STAFF';

    public static function values(): array
    {
        return array_map(fn (self $role): string => $role->value, self::cases());
    }
}
