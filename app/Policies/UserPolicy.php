<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;

class UserPolicy
{
    public function administer(User $actor): bool
    {
        return $actor->isActive() && $actor->hasRole(UserRole::HR_ADMIN);
    }

    public function viewCandidateProfile(User $actor, User $candidateUser): bool
    {
        return $actor->isActive()
            && $actor->hasRole(UserRole::CANDIDATE)
            && $actor->user_id === $candidateUser->user_id;
    }

    public function canChangeRole(User $actor, User $target, UserRole|string $newRole): bool
    {
        if (! $this->administer($actor)) {
            return false;
        }

        $newRoleValue = $newRole instanceof UserRole ? $newRole->value : $newRole;

        return ! $this->wouldRemoveLastActiveHrAdmin($target, $newRoleValue, $target->status->value);
    }

    public function canChangeStatus(User $actor, User $target, AccountStatus|string $newStatus): bool
    {
        if (! $this->administer($actor)) {
            return false;
        }

        $newStatusValue = $newStatus instanceof AccountStatus ? $newStatus->value : $newStatus;

        return ! $this->wouldRemoveLastActiveHrAdmin($target, $target->role->value, $newStatusValue);
    }

    public function wouldRemoveLastActiveHrAdmin(User $target, string $newRole, string $newStatus): bool
    {
        if (! $target->hasRole(UserRole::HR_ADMIN) || ! $target->isActive()) {
            return false;
        }

        if ($newRole === UserRole::HR_ADMIN->value && $newStatus === AccountStatus::ACTIVE->value) {
            return false;
        }

        return User::query()
            ->where('role', UserRole::HR_ADMIN->value)
            ->where('status', AccountStatus::ACTIVE->value)
            ->where('user_id', '!=', $target->user_id)
            ->doesntExist();
    }
}
