<?php

namespace Tests\Feature\Candidate;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class OpenJobBrowsingTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_sees_only_open_jobs(): void
    {
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $candidate = $this->user(UserRole::CANDIDATE, 'candidate@example.com');
        $department = Department::create(['name' => 'Engineering']);
        $open = $this->job($department, $hr, JobRequisitionStatus::OPEN, 'Open Laravel Role');
        $closed = $this->job($department, $hr, JobRequisitionStatus::CLOSED, 'Closed PHP Role');

        $this->actingAs($candidate)->get(route('candidate.jobs.index'))
            ->assertOk()
            ->assertSee($open->title)
            ->assertDontSee($closed->title);
    }

    private function user(UserRole $role, string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }

    private function job(Department $department, User $creator, JobRequisitionStatus $status, string $title): JobRequisition
    {
        return JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => $title,
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => $status,
            'created_by' => $creator->user_id,
        ]);
    }
}
