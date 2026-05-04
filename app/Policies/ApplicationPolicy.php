<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isActive() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN) || $user->hasRole(UserRole::CANDIDATE);
    }

    public function view(User $user, Application $application): bool
    {
        if ($user->hasRole(UserRole::HR_ADMIN)) {
            return true;
        }

        return $user->hasRole(UserRole::CANDIDATE)
            && $user->candidate?->candidate_id === $application->candidate_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::CANDIDATE);
    }

    public function update(User $user, Application $application): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }
}
