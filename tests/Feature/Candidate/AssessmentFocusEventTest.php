<?php

namespace Tests\Feature\Candidate;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentFocusEventTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_candidate_can_record_simulated_focus_loss_event(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question);

        $this->actingAs($candidate)->post(route('candidate.assessments.focus-events.store', $attempt), [
            'event_type' => 'FOCUS_LOST',
            'visible_state' => 'hidden',
        ])->assertRedirect(route('candidate.assessments.show', $attempt));

        $this->assertDatabaseHas('assessment_integrity_events', ['ca_id' => $attempt->ca_id, 'event_type' => 'FOCUS_LOST']);
    }
}
