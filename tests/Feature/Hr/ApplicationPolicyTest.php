<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ApplicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_policy_enforces_hr_access_candidate_ownership_inactive_and_wrong_role_denial(): void
    {
        [$application, $owner, $otherCandidate, $hr, $inactiveHr, $interviewer] = $this->records();

        $this->assertTrue($hr->can('view', $application));
        $this->assertTrue($hr->can('update', $application));
        $this->assertTrue($owner->can('view', $application));
        $this->assertFalse($owner->can('update', $application));
        $this->assertFalse($otherCandidate->can('view', $application));
        $this->assertFalse($inactiveHr->can('update', $application));
        $this->assertFalse($interviewer->can('view', $application));
    }

    /** @return array{0: Application, 1: User, 2: User, 3: User, 4: User, 5: User} */
    private function records(): array
    {
        $hr = $this->user(UserRole::HR_ADMIN, AccountStatus::ACTIVE, 'hr@example.com');
        $inactiveHr = $this->user(UserRole::HR_ADMIN, AccountStatus::INACTIVE, 'inactive@example.com');
        $interviewer = $this->user(UserRole::INTERVIEWER, AccountStatus::ACTIVE, 'interviewer@example.com');
        $owner = $this->candidate('owner@example.com');
        $other = $this->candidate('other@example.com');
        $department = Department::create(['name' => 'Engineering']);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);
        $application = Application::create([
            'candidate_id' => $owner->user_id,
            'job_id' => $job->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => 80,
            'applied_at' => now(),
        ]);

        return [$application, $owner, $other, $hr, $inactiveHr, $interviewer];
    }

    private function candidate(string $email): User
    {
        $user = $this->user(UserRole::CANDIDATE, AccountStatus::ACTIVE, $email);
        Candidate::create(['candidate_id' => $user->user_id, 'phone' => '+15551234567']);

        return $user;
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
}
