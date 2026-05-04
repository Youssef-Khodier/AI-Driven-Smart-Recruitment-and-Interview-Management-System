<?php

namespace Tests\Feature\Candidate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentPolicyTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_candidate_cannot_view_or_update_another_candidates_attempt(): void
    {
        [, , , $application, $assessment, $question] = $this->assessmentFixture();
        [, $otherCandidate] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question);
        $snapshot = $attempt->attemptQuestions->first();

        $this->actingAs($otherCandidate)->get(route('candidate.assessments.show', $attempt))->assertForbidden();
        $this->actingAs($otherCandidate)->put(route('candidate.assessments.answers.update', [$attempt, $snapshot]), [
            'answer_text' => 'Laravel',
        ])->assertForbidden();
    }
}
