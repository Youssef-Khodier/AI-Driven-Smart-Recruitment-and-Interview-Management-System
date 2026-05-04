<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\UserRole;

final class InterviewPolicy
{
    public function manage(array $user): bool
    {
        return ($user['role'] ?? null) === UserRole::HR_ADMIN->value && ($user['status'] ?? null) === AccountStatus::ACTIVE->value;
    }

    public function view(array $user, array $interview): bool
    {
        if ($this->manage($user)) {
            return true;
        }
        
        $userId = (int)$user['user_id'];
        $interviewId = (int)$interview['interview_id'];
        
        $assignment = \App\Repositories\InterviewRepository::findAssignment($interviewId, $userId);
        return $assignment !== null;
    }

    public function reschedule(array $user, array $interview): bool
    {
        return $this->manage($user);
    }

    public function complete(array $user, array $interview): bool
    {
        return $this->manage($user);
    }

    public function cancel(array $user, array $interview): bool
    {
        return $this->manage($user);
    }
}
