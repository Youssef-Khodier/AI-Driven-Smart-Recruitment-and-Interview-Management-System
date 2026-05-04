<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\AssessmentAttemptStatus;
use App\Enums\AssessmentQuestionType;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\Assessment;
use App\Models\Candidate;
use App\Models\CandidateAssessment;
use App\Models\CandidateAssessmentQuestion;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\Question;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait AssessmentTestHelpers
{
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

    private function assessmentFixture(ApplicationStatus $status = ApplicationStatus::ASSESSMENT): array
    {
        $department = Department::create(['name' => fake()->unique()->word()]);
        $hr = $this->user(UserRole::HR_ADMIN, fake()->unique()->safeEmail());
        $candidateUser = $this->user(UserRole::CANDIDATE, fake()->unique()->safeEmail());
        $candidate = Candidate::create([
            'candidate_id' => $candidateUser->user_id,
            'phone' => '+15551234567',
            'current_title' => 'Laravel Developer',
            'years_experience' => 3,
            'location' => 'Remote',
            'resume_url' => 'https://example.com/resume.pdf',
            'skill_keywords' => 'Laravel, PHP, MySQL',
        ]);
        $job = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Build systems.',
            'requirements' => 'Laravel PHP MySQL',
            'status' => JobRequisitionStatus::OPEN,
            'created_by' => $hr->user_id,
        ]);
        $application = Application::create([
            'candidate_id' => $candidate->candidate_id,
            'job_id' => $job->job_id,
            'status' => $status,
            'match_score' => 90,
            'applied_at' => now(),
        ]);
        $assessment = Assessment::create([
            'job_id' => $job->job_id,
            'title' => 'Technical Test',
            'description' => 'Answer all questions.',
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
        $question = Question::create([
            'assessment_id' => $assessment->assessment_id,
            'type' => AssessmentQuestionType::MCQ,
            'difficulty_level' => 'EASY',
            'question_text' => 'Choose Laravel.',
            'options' => ['Laravel', 'Django'],
            'correct_answer' => 'Laravel',
            'points' => 10,
            'is_active' => true,
        ]);

        return [$hr, $candidateUser->load('candidate'), $job, $application, $assessment, $question];
    }

    private function startedAttempt(Application $application, Assessment $assessment, Question $question, ?\Illuminate\Support\Carbon $expiresAt = null): CandidateAssessment
    {
        $attempt = CandidateAssessment::create([
            'application_id' => $application->application_id,
            'candidate_id' => $application->candidate_id,
            'assessment_id' => $assessment->assessment_id,
            'start_time' => now()->subMinute(),
            'expires_at' => $expiresAt ?? now()->addMinutes(30),
            'status' => AssessmentAttemptStatus::IN_PROGRESS,
        ]);
        CandidateAssessmentQuestion::create([
            'ca_id' => $attempt->ca_id,
            'question_id' => $question->question_id,
            'display_order' => 1,
            'question_type' => $question->type,
            'question_text' => $question->question_text,
            'options' => $question->options,
            'correct_answer' => $question->correct_answer,
            'points' => $question->points,
        ]);

        return $attempt->load('attemptQuestions');
    }

    private function saveAnswer(CandidateAssessment $attempt, string $answer = 'Laravel', ?\Illuminate\Support\Carbon $savedAt = null): Submission
    {
        $snapshot = $attempt->attemptQuestions()->firstOrFail();

        return Submission::create([
            'ca_id' => $attempt->ca_id,
            'attempt_question_id' => $snapshot->attempt_question_id,
            'question_id' => $snapshot->question_id,
            'answer_text' => $answer,
            'saved_at' => $savedAt ?? now(),
        ]);
    }
}
