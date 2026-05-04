<?php

namespace Tests\Feature\Candidate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentLateSubmissionTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_late_answer_changes_are_rejected(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question, now()->subMinute());
        $snapshot = $attempt->attemptQuestions->first();

        $this->actingAs($candidate)->put(route('candidate.assessments.answers.update', [$attempt, $snapshot]), [
            'answer_text' => 'Laravel',
        ])->assertSessionHasErrors('assessment');

        $this->assertDatabaseMissing('submissions', ['ca_id' => $attempt->ca_id, 'answer_text' => 'Laravel']);
    }
}
