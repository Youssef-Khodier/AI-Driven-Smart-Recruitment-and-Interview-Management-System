<?php

namespace Tests\Feature\Foundation;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\AssessmentAttemptStatus;
use App\Enums\AssessmentQuestionType;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\AssessmentIntegrityEvent;
use App\Models\Candidate;
use App\Models\CandidateAssessment;
use App\Models\CandidateAssessmentQuestion;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AssessmentModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_assessment_models_have_expected_relationships(): void
    {
        [$application, $job, $candidate] = $this->applicationFixture();
        $assessment = Assessment::create(['job_id' => $job->job_id, 'title' => 'Technical', 'duration_minutes' => 30]);
        $question = Question::create(['assessment_id' => $assessment->assessment_id, 'type' => AssessmentQuestionType::MCQ, 'difficulty_level' => 'EASY', 'question_text' => 'Pick A', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 2]);
        $attempt = CandidateAssessment::create(['application_id' => $application->application_id, 'candidate_id' => $candidate->candidate_id, 'assessment_id' => $assessment->assessment_id, 'status' => AssessmentAttemptStatus::IN_PROGRESS, 'start_time' => now(), 'expires_at' => now()->addMinute()]);
        $snapshot = CandidateAssessmentQuestion::create(['ca_id' => $attempt->ca_id, 'question_id' => $question->question_id, 'display_order' => 1, 'question_type' => AssessmentQuestionType::MCQ, 'question_text' => 'Pick A', 'options' => ['A', 'B'], 'correct_answer' => 'A', 'points' => 2]);
        Submission::create(['ca_id' => $attempt->ca_id, 'attempt_question_id' => $snapshot->attempt_question_id, 'question_id' => $question->question_id, 'answer_text' => 'A', 'saved_at' => now()]);
        AssessmentIntegrityEvent::create(['ca_id' => $attempt->ca_id, 'event_type' => 'FOCUS_LOST', 'occurred_at' => now()]);

        $this->assertTrue($job->assessments()->whereKey($assessment->assessment_id)->exists());
        $this->assertTrue($assessment->questions()->whereKey($question->question_id)->exists());
        $this->assertTrue($attempt->attemptQuestions()->whereKey($snapshot->attempt_question_id)->exists());
        $this->assertTrue($attempt->submissions()->exists());
        $this->assertTrue($attempt->integrityEvents()->exists());
    }

    private function applicationFixture(): array
    {
        $department = Department::create(['name' => 'Engineering']);
        $hr = $this->user(UserRole::HR_ADMIN, 'hr@example.com');
        $candidateUser = $this->user(UserRole::CANDIDATE, 'candidate@example.com');
        $candidate = Candidate::create(['candidate_id' => $candidateUser->user_id, 'years_experience' => 2]);
        $job = JobRequisition::create(['department_id' => $department->department_id, 'title' => 'Developer', 'description' => 'D', 'requirements' => 'R', 'created_by' => $hr->user_id]);
        $application = Application::create(['candidate_id' => $candidate->candidate_id, 'job_id' => $job->job_id, 'status' => ApplicationStatus::ASSESSMENT, 'match_score' => 80, 'applied_at' => now()]);

        return [$application, $job, $candidate];
    }

    private function user(UserRole $role, string $email): User
    {
        return User::create(['name' => $email, 'email' => $email, 'password_hash' => Hash::make('password'), 'role' => $role, 'status' => AccountStatus::ACTIVE]);
    }
}
