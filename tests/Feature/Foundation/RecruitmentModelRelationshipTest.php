<?php

namespace Tests\Feature\Foundation;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\ApplicationStatusHistory;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\JobRequisitionStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RecruitmentModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_recruitment_model_relationships_are_available(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $hr = User::create([
            'department_id' => $department->department_id,
            'name' => 'HR Admin',
            'email' => 'hr@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::HR_ADMIN,
            'status' => AccountStatus::ACTIVE,
        ]);
        $candidateUser = User::create([
            'name' => 'Candidate One',
            'email' => 'candidate@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => AccountStatus::ACTIVE,
        ]);
        $candidate = Candidate::create([
            'candidate_id' => $candidateUser->user_id,
            'phone' => '+15551234567',
            'skill_keywords' => 'php, laravel',
        ]);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Build recruitment workflows.',
            'requirements' => 'Laravel PHP with 2 years experience.',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);
        $application = Application::create([
            'candidate_id' => $candidate->candidate_id,
            'job_id' => $job->job_id,
            'status' => ApplicationStatus::APPLIED,
            'match_score' => 95,
            'applied_at' => now(),
        ]);
        $jobHistory = JobRequisitionStatusHistory::create([
            'job_id' => $job->job_id,
            'actor_user_id' => $hr->user_id,
            'old_status' => JobRequisitionStatus::APPROVED,
            'new_status' => JobRequisitionStatus::OPEN,
        ]);
        $applicationHistory = ApplicationStatusHistory::create([
            'application_id' => $application->application_id,
            'actor_user_id' => $hr->user_id,
            'old_status' => ApplicationStatus::APPLIED,
            'new_status' => ApplicationStatus::SCREENING,
        ]);

        $this->assertTrue($department->jobRequisitions->first()->is($job));
        $this->assertTrue($hr->createdJobRequisitions->first()->is($job));
        $this->assertTrue($candidate->applications->first()->is($application));
        $this->assertTrue($application->candidate->is($candidate));
        $this->assertTrue($application->jobRequisition->is($job));
        $this->assertTrue($job->statusHistories->first()->is($jobHistory));
        $this->assertTrue($application->statusHistories->first()->is($applicationHistory));
        $this->assertTrue($jobHistory->actor->is($hr));
        $this->assertTrue($applicationHistory->actor->is($hr));
    }
}
