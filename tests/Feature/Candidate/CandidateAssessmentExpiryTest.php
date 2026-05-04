<?php

namespace Tests\Feature\Candidate;

use App\Enums\AssessmentAttemptStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentExpiryTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_showing_expired_attempt_marks_it_expired(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question, now()->subMinute());
        $this->saveAnswer($attempt, 'Laravel', now()->subMinutes(2));

        $this->actingAs($candidate)->get(route('candidate.assessments.show', $attempt))->assertRedirect(route('candidate.assessments.result', $attempt));

        $this->assertSame(AssessmentAttemptStatus::EXPIRED, $attempt->refresh()->status);
    }
}
