<?php

namespace Tests\Feature\Hr;

use App\Enums\ApplicationStatus;
use App\Enums\UserRole;
use App\Models\Application;
use App\Models\AssessmentIntegrityEvent;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentResultsReviewTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_hr_can_review_assessment_results_with_focus_loss_count(): void
    {
        [$hr, , $job, $application, $assessment, $question] = $this->assessmentFixture();
        $attempt = $this->startedAttempt($application, $assessment, $question);
        AssessmentIntegrityEvent::create(['ca_id' => $attempt->ca_id, 'event_type' => 'FOCUS_LOST', 'occurred_at' => now()]);

        $this->actingAs($hr)->get(route('hr.assessment-results.index', $job))
            ->assertOk()
            ->assertSee('Simulated')
            ->assertSee('1');
    }

    public function test_hr_results_page_handles_fifty_attempts(): void
    {
        [$hr, , $job, $application, $assessment, $question] = $this->assessmentFixture();
        $this->startedAttempt($application, $assessment, $question);

        for ($i = 0; $i < 49; $i++) {
            $candidateUser = $this->user(UserRole::CANDIDATE, "candidate{$i}@example.com");
            Candidate::create(['candidate_id' => $candidateUser->user_id, 'years_experience' => 2]);
            $candidateApplication = Application::create([
                'candidate_id' => $candidateUser->user_id,
                'job_id' => $job->job_id,
                'status' => ApplicationStatus::ASSESSMENT,
                'match_score' => 75,
                'applied_at' => now(),
            ]);
            $this->startedAttempt($candidateApplication, $assessment, $question);
        }

        $this->actingAs($hr)->get(route('hr.assessment-results.index', $job))->assertOk()->assertSee('candidate48@example.com');
    }
}
