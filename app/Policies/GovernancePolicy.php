<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;

final class GovernancePolicy
{
    public function viewApprovalQueue(array $user): bool
    {
        return $this->isHrAdmin($user) && ($user['is_department_head'] ?? false);
    }

    public function approveRequisition(array $user, array $requisition): bool
    {
        return $this->isHrAdmin($user)
            && ($user['is_department_head'] ?? false)
            && (int) $user['department_id'] === (int) $requisition['department_id']
            && (int) $user['user_id'] !== (int) $requisition['created_by']
            && $requisition['status'] === JobRequisitionStatus::PENDING->value;
    }

    public function publishRequisition(array $user, array $requisition): bool
    {
        return $this->isHrAdmin($user) && $requisition['status'] === JobRequisitionStatus::OPEN->value;
    }

    public function viewGovernance(array $user): bool
    {
        return $this->isHrAdmin($user);
    }

    public function manageDepartmentHeads(array $user): bool
    {
        return $this->isHrAdmin($user);
    }

    private function isHrAdmin(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }
}
