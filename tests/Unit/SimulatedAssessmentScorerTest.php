<?php

namespace Tests\Unit;

use App\Enums\AssessmentAttemptStatus;
use App\Enums\AssessmentQuestionType;
use App\Models\Assessment;
use App\Models\CandidateAssessment;
use App\Models\CandidateAssessmentQuestion;
use App\Models\Submission;
use App\Support\SimulatedAssessmentScorer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class SimulatedAssessmentScorerTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_scores_mcq_and_text_answers_deterministically(): void
    {
        [, , , $application, $assessment] = $this->assessmentFixture();
        $attempt = CandidateAssessment::forceCreate([
            'application_id' => $application->application_id,
            'candidate_id' => $application->candidate_id,
            'assessment_id' => $assessment->assessment_id,
            'status' => AssessmentAttemptStatus::IN_PROGRESS,
        ]);
        $mcq = CandidateAssessmentQuestion::create([
            'ca_id' => $attempt->ca_id,
            'display_order' => 1,
            'question_type' => AssessmentQuestionType::MCQ,
            'question_text' => 'Pick one',
            'options' => ['A', 'B'],
            'correct_answer' => 'A',
            'points' => 5,
        ]);
        $text = CandidateAssessmentQuestion::create([
            'ca_id' => $attempt->ca_id,
            'display_order' => 2,
            'question_type' => AssessmentQuestionType::THEORY,
            'question_text' => 'Explain',
            'correct_answer' => 'laravel php mysql',
            'points' => 5,
        ]);
        Submission::create(['ca_id' => $attempt->ca_id, 'attempt_question_id' => $mcq->attempt_question_id, 'answer_text' => 'A', 'saved_at' => now()]);
        Submission::create(['ca_id' => $attempt->ca_id, 'attempt_question_id' => $text->attempt_question_id, 'answer_text' => 'Laravel and PHP', 'saved_at' => now()]);

        $this->assertSame(83, app(SimulatedAssessmentScorer::class)->score($attempt));
    }
}
