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
        
        $assignment = \App\Models\InterviewModel::findAssignment($interviewId, $userId);
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

    public function viewAudit(array $user, array $interview): bool
    {
        return $this->manage($user);
    }

    public function recommendPanel(array $user): bool
    {
        return $this->manage($user);
    }

    public function manageWorkspace(array $user, array $interview): bool
    {
        return $this->manage($user);
    }

    public function decideExtension(array $user, array $interview): bool
    {
        return $this->manage($user);
    }

    public function requestExtension(array $user, array $interview): bool
    {
        if ($this->manage($user)) {
            return false;
        }
        $userId = (int)$user['user_id'];
        $assignment = \App\Models\InterviewModel::findAssignment((int)$interview['interview_id'], $userId);
        if (!$assignment) {
            return false;
        }
        
        return empty($assignment['is_shadowing']);
    }

    public function updateWorkspace(array $user, array $interview): bool
    {
        $userId = (int)$user['user_id'];
        $assignment = \App\Models\InterviewModel::findAssignment((int)$interview['interview_id'], $userId);
        if (!$assignment) {
            return false;
        }
        
        return empty($assignment['is_shadowing']);
    }
}
