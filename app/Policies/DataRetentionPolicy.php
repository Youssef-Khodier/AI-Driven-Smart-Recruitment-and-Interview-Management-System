<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Enums\ApplicationStatus;

final class DataRetentionPolicy
{
    public function performAction(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value 
            && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }
    
    public function isEligibleForRetention(array $candidateLastApplication): bool
    {
        $status = $candidateLastApplication['status'] ?? null;
        $reqStatus = $candidateLastApplication['job_status'] ?? null;
        
        if ($status === ApplicationStatus::REJECTED->value) {
            return true;
        }
        
        if ($reqStatus === 'CLOSED') {
            return true;
        }
        
        return false;
    }
}