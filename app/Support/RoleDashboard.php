<?php

namespace App\Support;

use App\Enums\UserRole;
use App\Models\User;

class RoleDashboard
{
    public static function routeNameFor(User $user): string
    {
        return match ($user->role) {
            UserRole::HR_ADMIN => 'hr.dashboard',
            UserRole::INTERVIEWER => 'interviewer.dashboard',
            UserRole::CANDIDATE => 'candidate.dashboard',
        };
    }
}
