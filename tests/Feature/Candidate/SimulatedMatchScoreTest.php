<?php

namespace Tests\Feature\Candidate;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SimulatedMatchScoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_application_persists_simulated_advisory_match_score(): void
    {
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $candidate = $this->user(UserRole::CANDIDATE, 'candidate@example.com');
        Candidate::create([
            'candidate_id' => $candidate->user_id,
            'phone' => '+15551234567',
            'current_title' => 'Laravel Developer',
            'years_experience' => 4,
            'location' => 'Remote',
            'resume_url' => 'https://example.com/resume.pdf',
            'skill_keywords' => 'Laravel, PHP, MySQL',
        ]);
        $department = Department::create(['name' => 'Engineering']);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Description',
            'requirements' => 'Laravel PHP MySQL developer with 4 years experience.',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);

        $this->actingAs($candidate)->post(route('candidate.applications.store', $job))->assertRedirect();

        $this->assertDatabaseHas('applications', [
            'candidate_id' => $candidate->user_id,
            'job_id' => $job->job_id,
            'match_score' => 100,
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
