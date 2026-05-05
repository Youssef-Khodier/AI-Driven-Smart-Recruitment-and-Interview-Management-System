<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\UserRole;

final class ReportPolicy
{
    public function viewPipeline(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value 
            && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }

    public function viewTimeToHire(array $user): bool
    {
        return $this->viewPipeline($user);
    }
}