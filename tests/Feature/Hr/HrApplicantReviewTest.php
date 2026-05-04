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

class HrApplicantReviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_review_applicants_sorted_by_score(): void
    {
        [$hr, $job] = $this->job();
        $low = $this->application($job, 'low@example.com', 20);
        $high = $this->application($job, 'high@example.com', 95);

        $response = $this->actingAs($hr)->get(route('hr.applications.index', $job));

        $response->assertOk()->assertSee('95 simulated advisory')->assertSee('20 simulated advisory');
        $this->assertLessThan(
            strpos($response->getContent(), $low->candidate->user->email),
            strpos($response->getContent(), $high->candidate->user->email)
        );
    }

    /** @return array{0: User, 1: JobRequisition} */
    private function job(): array
    {
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $department = Department::create(['name' => 'Engineering']);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);

        return [$hr, $job];
    }

    private function application(JobRequisition $job, string $email, int $score): Application
    {
        $user = $this->user(UserRole::CANDIDATE, $email);
        Candidate::create(['candidate_id' => $user->user_id, 'phone' => '+15551234567', 'current_title' => 'Developer']);

        return Application::create([
            'candidate_id' => $user->user_id,
            'job_id' => $job->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => $score,
            'applied_at' => now(),
        ]);
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
