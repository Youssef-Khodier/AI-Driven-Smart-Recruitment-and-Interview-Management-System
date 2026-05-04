<?php

namespace App\Policies;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;

final class ApplicationPolicy
{
    public function transition(array $user, array $application, string $nextStatus): bool
    {
        if (($user['role'] ?? null) !== UserRole::HR_ADMIN->value || ($user['status'] ?? null) !== AccountStatus::ACTIVE->value) {
            return false;
        }

        $allowed = [
            ApplicationStatus::APPLIED->value => [ApplicationStatus::SCREENING->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::SCREENING->value => [ApplicationStatus::ASSESSMENT->value, ApplicationStatus::INTERVIEW->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::ASSESSMENT->value => [ApplicationStatus::INTERVIEW->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::INTERVIEW->value => [ApplicationStatus::OFFER->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::OFFER->value => [ApplicationStatus::HIRED->value, ApplicationStatus::REJECTED->value],
            ApplicationStatus::REJECTED->value => [],
            ApplicationStatus::HIRED->value => [],
        ];

        return in_array($nextStatus, $allowed[$application['status']] ?? [], true);
    }
}
