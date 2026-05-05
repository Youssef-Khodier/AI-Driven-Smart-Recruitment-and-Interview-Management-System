<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\UserRole;

final class AuditLogPolicy
{
    public function view(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value 
            && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }
}