<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Policies\ApplicationPolicy;
use PHPUnit\Framework\TestCase;

final class ApplicationPolicyTest extends TestCase
{
    public function testActiveHrAdminCanMoveAppliedApplicationToScreening(): void
    {
        $policy = new ApplicationPolicy();

        $this->assertTrue($policy->transition(
            ['role' => UserRole::HR_ADMIN->value, 'status' => AccountStatus::ACTIVE->value],
            ['status' => ApplicationStatus::APPLIED->value],
            ApplicationStatus::SCREENING->value
        ));
    }

    public function testCandidateCannotTransitionApplication(): void
    {
        $policy = new ApplicationPolicy();

        $this->assertFalse($policy->transition(
            ['role' => UserRole::CANDIDATE->value, 'status' => AccountStatus::ACTIVE->value],
            ['status' => ApplicationStatus::APPLIED->value],
            ApplicationStatus::SCREENING->value
        ));
    }

    public function testInactiveHrAdminCannotTransitionApplication(): void
    {
        $policy = new ApplicationPolicy();

        $this->assertFalse($policy->transition(
            ['role' => UserRole::HR_ADMIN->value, 'status' => AccountStatus::INACTIVE->value],
            ['status' => ApplicationStatus::APPLIED->value],
            ApplicationStatus::SCREENING->value
        ));
    }

    public function testItRejectsInvalidStatusTransition(): void
    {
        $policy = new ApplicationPolicy();

        $this->assertFalse($policy->transition(
            ['role' => UserRole::HR_ADMIN->value, 'status' => AccountStatus::ACTIVE->value],
            ['status' => ApplicationStatus::REJECTED->value],
            ApplicationStatus::HIRED->value
        ));
    }
}
