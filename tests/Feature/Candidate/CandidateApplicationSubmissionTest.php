<?php

namespace Tests\Feature\Candidate;

use App\Enums\AccountStatus;
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

class CandidateApplicationSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_apply_once_to_open_job(): void
    {
        [$candidate, $job] = $this->candidateAndJob(JobRequisitionStatus::OPEN);

        $this->actingAs($candidate)->post(route('candidate.applications.store', $job))->assertRedirect();
        $this->assertDatabaseCount('applications', 1);

        $application = Application::firstOrFail();
        $this->actingAs($candidate)->post(route('candidate.applications.store', $job))
            ->assertRedirect(route('candidate.applications.show', $application))
            ->assertSessionHasErrors('duplicate');
        $this->assertDatabaseCount('applications', 1);
    }

    public function test_candidate_cannot_apply_to_closed_job_or_with_incomplete_profile(): void
    {
        [$candidate, $closedJob] = $this->candidateAndJob(JobRequisitionStatus::CLOSED);
        $this->actingAs($candidate)->post(route('candidate.applications.store', $closedJob))
            ->assertSessionHasErrors('requisition');

        [$incompleteCandidate, $openJob] = $this->candidateAndJob(JobRequisitionStatus::OPEN, false);
        $this->actingAs($incompleteCandidate)->post(route('candidate.applications.store', $openJob))
            ->assertSessionHasErrors('profile');
    }

    /**
     * @return array{0: User, 1: JobRequisition}
     */
    private function candidateAndJob(JobRequisitionStatus $status, bool $completeProfile = true): array
    {
        $hr = $this->user(UserRole::HR_ADMIN, fake()->unique()->safeEmail());
        $candidate = $this->user(UserRole::CANDIDATE, fake()->unique()->safeEmail());
        Candidate::create([
            'candidate_id' => $candidate->user_id,
            'phone' => '+15551234567',
            'current_title' => $completeProfile ? 'Laravel Developer' : null,
            'years_experience' => 4,
            'location' => $completeProfile ? 'Remote' : null,
            'resume_url' => $completeProfile ? 'https://example.com/resume.pdf' : null,
            'skill_keywords' => $completeProfile ? 'Laravel, PHP' : null,
        ]);
        $department = Department::create(['name' => fake()->unique()->word()]);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Description',
            'requirements' => 'Laravel PHP with 3 years experience.',
            'status' => $status,
            'created_by' => $hr->user_id,
        ]);

        return [$candidate->load('candidate'), $job];
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
