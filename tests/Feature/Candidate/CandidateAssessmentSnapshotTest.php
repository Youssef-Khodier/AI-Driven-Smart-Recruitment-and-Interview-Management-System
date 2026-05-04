<?php

namespace Tests\Feature\Candidate;

use App\Models\CandidateAssessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class CandidateAssessmentSnapshotTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_start_snapshots_questions_and_blocks_duplicate_attempts(): void
    {
        [, $candidate, , $application, $assessment, $question] = $this->assessmentFixture();

        $this->actingAs($candidate)->post(route('candidate.assessments.start', [$application, $assessment]))->assertRedirect();
        $attempt = CandidateAssessment::firstOrFail();
        $this->assertDatabaseHas('candidate_assessment_questions', [
            'ca_id' => $attempt->ca_id,
            'question_text' => $question->question_text,
            'display_order' => 1,
        ]);

        $question->update(['question_text' => 'Edited after start']);
        $this->assertDatabaseHas('candidate_assessment_questions', ['ca_id' => $attempt->ca_id, 'question_text' => 'Choose Laravel.']);

        $this->actingAs($candidate)->post(route('candidate.assessments.start', [$application, $assessment]))
            ->assertRedirect(route('candidate.assessments.show', $attempt));
    }
}
