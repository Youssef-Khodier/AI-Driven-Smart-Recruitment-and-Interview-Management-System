<?php

namespace App\Policies;

use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\JobRequisition;
use App\Models\User;

class JobRequisitionPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isActive() ? null : false;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN) || $user->hasRole(UserRole::CANDIDATE);
    }

    public function view(User $user, JobRequisition $jobRequisition): bool
    {
        if ($user->hasRole(UserRole::HR_ADMIN)) {
            return true;
        }

        return $user->hasRole(UserRole::CANDIDATE)
            && $jobRequisition->status === JobRequisitionStatus::OPEN;
    }

    public function create(User $user): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }

    public function update(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN)
            && in_array($jobRequisition->status, [
                JobRequisitionStatus::DRAFT,
                JobRequisitionStatus::PENDING_APPROVAL,
                JobRequisitionStatus::APPROVED,
            ], true);
    }

    public function submit(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN)
            && $jobRequisition->status === JobRequisitionStatus::DRAFT;
    }

    public function approve(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN)
            && $jobRequisition->status === JobRequisitionStatus::PENDING_APPROVAL
            && $jobRequisition->created_by !== $user->user_id;
    }

    public function open(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN)
            && $jobRequisition->status === JobRequisitionStatus::APPROVED;
    }

    public function close(User $user, JobRequisition $jobRequisition): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN)
            && in_array($jobRequisition->status, [JobRequisitionStatus::APPROVED, JobRequisitionStatus::OPEN], true);
    }
}
