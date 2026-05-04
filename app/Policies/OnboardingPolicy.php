<?php

namespace App\Policies;

use App\Core\Auth;
use App\Enums\UserRole;

final class OnboardingPolicy
{
    public static function viewAny(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function view(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function create(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function update(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }
}
