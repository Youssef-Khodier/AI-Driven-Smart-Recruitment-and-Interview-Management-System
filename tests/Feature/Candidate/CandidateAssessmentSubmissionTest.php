<?php

namespace Tests\Feature\Candidate;

use App\Enums\AssessmentAttemptStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentSubmissionTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_candidate_can_save_answer_submit_and_see_simulated_score(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question);
        $snapshot = $attempt->attemptQuestions->first();

        $this->actingAs($candidate)->put(route('candidate.assessments.answers.update', [$attempt, $snapshot]), [
            'answer_text' => 'Laravel',
        ])->assertRedirect(route('candidate.assessments.show', $attempt));

        $this->actingAs($candidate)->post(route('candidate.assessments.submit', $attempt))
            ->assertRedirect(route('candidate.assessments.result', $attempt));

        $attempt->refresh();
        $this->assertSame(AssessmentAttemptStatus::SUBMITTED, $attempt->status);
        $this->assertSame('100.000', $attempt->score);
    }
}
