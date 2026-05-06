<?php

namespace App\Policies;

use App\Core\Auth;
use App\Enums\UserRole;

final class FinalEvaluationPolicy
{
    public static function view(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function create(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function viewGovernance(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function recalculateGovernance(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function reviewFlags(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function resolveFlag(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function viewDebrief(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function completeDebrief(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }

    public static function maintainBenchmarks(): bool
    {
        return Auth::hasRole(UserRole::HR_ADMIN->value);
    }
}
