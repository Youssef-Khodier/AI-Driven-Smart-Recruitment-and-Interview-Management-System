<?php

namespace Tests\Feature\Candidate;

use App\Enums\ApplicationStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentStartTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_candidate_can_start_assessment_only_in_assessment_status(): void
    {
        [, $candidate, , $application, $assessment] = $this->assessmentFixture();

        $this->actingAs($candidate)->post(route('candidate.assessments.start', [$application, $assessment]))->assertRedirect();
        $this->assertDatabaseHas('candidate_assessments', ['application_id' => $application->application_id]);

        [, $otherCandidate, , $screeningApplication, $otherAssessment] = $this->assessmentFixture(ApplicationStatus::SCREENING);
        $this->actingAs($otherCandidate)->post(route('candidate.assessments.start', [$screeningApplication, $otherAssessment]))
            ->assertSessionHasErrors('assessment');
    }
}
