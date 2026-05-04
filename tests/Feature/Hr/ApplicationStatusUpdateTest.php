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

class ApplicationStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_updates_application_status_and_history(): void
    {
        [$hr, $application] = $this->application();

        $this->actingAs($hr)->put(route('hr.applications.update', $application), [
            'status' => ApplicationStatus::INTERVIEW->value,
            'reason' => 'Strong simulated match.',
        ])->assertRedirect();

        $this->assertSame(ApplicationStatus::INTERVIEW, $application->refresh()->status);
        $this->assertDatabaseHas('application_status_histories', [
            'application_id' => $application->application_id,
            'actor_user_id' => $hr->user_id,
            'old_status' => ApplicationStatus::APPLIED->value,
            'new_status' => ApplicationStatus::INTERVIEW->value,
            'reason' => 'Strong simulated match.',
        ]);
    }

    /** @return array{0: User, 1: Application} */
    private function application(): array
    {
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $candidateUser = $this->user(UserRole::CANDIDATE, 'candidate@example.com');
        Candidate::create(['candidate_id' => $candidateUser->user_id, 'phone' => '+15551234567']);
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
            'candidate_id' => $candidateUser->user_id,
            'job_id' => $job->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => 80,
            'applied_at' => now(),
        ]);

        return [$hr, $application];
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
}
