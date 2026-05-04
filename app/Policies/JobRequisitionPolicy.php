<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;

final class JobRequisitionPolicy
{
    public function update(array $user, array $requisition): bool
    {
        return $this->isHrAdmin($user)
            && in_array($requisition['status'], [
                JobRequisitionStatus::DRAFT->value,
                JobRequisitionStatus::PENDING->value,
                JobRequisitionStatus::APPROVED->value,
            ], true);
    }

    public function transition(array $user, array $requisition, string $nextStatus): bool
    {
        if (! $this->isHrAdmin($user)) {
            return false;
        }

        return match ($nextStatus) {
            JobRequisitionStatus::PENDING->value => $requisition['status'] === JobRequisitionStatus::DRAFT->value,
            JobRequisitionStatus::APPROVED->value => $requisition['status'] === JobRequisitionStatus::PENDING->value
                && (int) $requisition['created_by'] !== (int) $user['user_id'],
            JobRequisitionStatus::OPEN->value => $requisition['status'] === JobRequisitionStatus::APPROVED->value,
            JobRequisitionStatus::CLOSED->value => in_array($requisition['status'], [JobRequisitionStatus::APPROVED->value, JobRequisitionStatus::OPEN->value], true),
            default => false,
        };
    }

    private function isHrAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }
}
