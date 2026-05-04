<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JobRequisitionPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_policy_enforces_roles_self_approval_inactive_and_candidate_open_visibility(): void
    {
        $hr = $this->user(UserRole::HR_ADMIN, AccountStatus::ACTIVE, 'hr@example.com');
        $otherHr = $this->user(UserRole::HR_ADMIN, AccountStatus::ACTIVE, 'other@example.com');
        $inactiveHr = $this->user(UserRole::HR_ADMIN, AccountStatus::INACTIVE, 'inactive@example.com');
        $candidate = $this->user(UserRole::CANDIDATE, AccountStatus::ACTIVE, 'candidate@example.com');
        $department = Department::create(['name' => 'Engineering']);
        $pending = $this->job($department, $hr, JobRequisitionStatus::PENDING_APPROVAL);
        $open = $this->job($department, $hr, JobRequisitionStatus::OPEN);
        $closed = $this->job($department, $hr, JobRequisitionStatus::CLOSED);

        $this->assertTrue($hr->can('create', JobRequisition::class));
        $this->assertFalse($candidate->can('create', JobRequisition::class));
        $this->assertFalse($hr->can('approve', $pending));
        $this->assertTrue($otherHr->can('approve', $pending));
        $this->assertFalse($inactiveHr->can('approve', $pending));
        $this->assertTrue($candidate->can('view', $open));
        $this->assertFalse($candidate->can('view', $closed));
    }

    private function user(UserRole $role, AccountStatus $status, string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }

    private function job(Department $department, User $creator, JobRequisitionStatus $status): JobRequisition
    {
        return JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => $status->value.' Job',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => $status,
            'created_by' => $creator->user_id,
        ]);
    }
}
