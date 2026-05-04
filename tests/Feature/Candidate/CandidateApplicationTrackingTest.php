<?php

namespace Tests\Feature\Candidate;

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

class CandidateApplicationTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_tracks_only_own_applications(): void
    {
        [$candidate, $own, $other] = $this->applications();

        $this->actingAs($candidate)->get(route('candidate.applications.index'))
            ->assertOk()
            ->assertSee($own->jobRequisition->title)
            ->assertDontSee($other->jobRequisition->title);

        $this->actingAs($candidate)->get(route('candidate.applications.show', $own))
            ->assertOk()
            ->assertSee(ApplicationStatus::SCREENING->value)
            ->assertSee('simulated advisory');

        $this->actingAs($candidate)->get(route('candidate.applications.show', $other))->assertForbidden();
    }

    /** @return array{0: User, 1: Application, 2: Application} */
    private function applications(): array
    {
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $candidate = $this->candidate('candidate@example.com');
        $otherCandidate = $this->candidate('other@example.com');
        $department = Department::create(['name' => 'Engineering']);
        $ownJob = $this->job($department, $hr, 'Own Application Job');
        $otherJob = $this->job($department, $hr, 'Other Application Job');

        $own = Application::create([
            'candidate_id' => $candidate->user_id,
            'job_id' => $ownJob->job_id,
            'status' => ApplicationStatus::SCREENING,
            'match_score' => 85,
            'applied_at' => now(),
        ]);
        $other = Application::create([
            'candidate_id' => $otherCandidate->user_id,
            'job_id' => $otherJob->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => 65,
            'applied_at' => now(),
        ]);

        return [$candidate, $own, $other];
    }

    private function job(Department $department, User $hr, string $title): JobRequisition
    {
        return JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => $title,
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);
    }

    private function candidate(string $email): User
    {
        $user = $this->user(UserRole::CANDIDATE, $email);
        Candidate::create(['candidate_id' => $user->user_id, 'phone' => '+15551234567']);

        return $user;
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
