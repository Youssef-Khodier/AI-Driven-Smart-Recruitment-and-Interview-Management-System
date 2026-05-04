<?php

namespace Tests\Feature\Hr;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentAttemptPrivacyTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_hr_can_review_attempt_but_candidate_cannot_review_other_candidate_attempt(): void
    {
        [$hr, , , $application, $assessment, $question] = $this->assessmentFixture();
        [, $otherCandidate] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question);

        $this->actingAs($hr)->get(route('hr.candidate-assessments.show', $attempt))->assertOk()->assertSee('Attempt Detail');
        $this->actingAs($otherCandidate)->get(route('candidate.assessments.result', $attempt))->assertForbidden();
    }
}
