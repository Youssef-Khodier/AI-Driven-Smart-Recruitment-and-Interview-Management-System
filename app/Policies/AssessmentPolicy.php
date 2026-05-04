<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Assessment;
use App\Models\User;

class AssessmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isActive() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }

    public function view(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }

    public function update(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }

    public function deactivate(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN) && $assessment->is_active;
    }

    public function reviewResults(User $user, Assessment $assessment): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }
}
