<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\InterviewAssignmentRole;
use App\Enums\InterviewStatus;

final class InterviewFeedbackPolicy
{
    public function create(array $user, array $interview, ?array $assignment, bool $alreadySubmitted): bool
    {
        if (($user['status'] ?? null) !== AccountStatus::ACTIVE->value) {
            return false;
        }

        if (($interview['status'] ?? null) !== InterviewStatus::COMPLETED->value) {
            return false;
        }

        if (!$assignment) {
            return false;
        }

        if (!in_array($assignment['role_in_panel'], InterviewAssignmentRole::officialScorerValues())) {
            return false;
        }

        if ($alreadySubmitted) {
            return false;
        }

        return true;
    }

    public function view(array $user, array $interview): bool
    {
        return (new InterviewPolicy())->view($user, $interview);
    }
}
