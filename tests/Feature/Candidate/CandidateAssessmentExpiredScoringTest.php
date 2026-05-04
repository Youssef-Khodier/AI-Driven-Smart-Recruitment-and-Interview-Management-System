<?php

namespace Tests\Feature\Candidate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentExpiredScoringTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_expired_attempt_scores_only_answers_saved_before_deadline(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();
        $deadline = now()->subMinute();
        $attempt = $this->startedAttempt($application, $assessment, $question, $deadline);
        $this->saveAnswer($attempt, 'Laravel', $deadline->copy()->subMinute());

        $this->actingAs($candidate)->post(route('candidate.assessments.submit', $attempt))->assertSessionHasErrors('assessment');

        $this->assertSame('100.000', $attempt->refresh()->score);
    }
}
