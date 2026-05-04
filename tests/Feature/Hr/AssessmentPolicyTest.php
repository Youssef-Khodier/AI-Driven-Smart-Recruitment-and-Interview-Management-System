<?php

namespace Tests\Feature\Hr;

use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentPolicyTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_only_hr_can_author_assessments(): void
    {
        [$hr, $candidate, $job, , $assessment] = $this->assessmentFixture();
        $interviewer = $this->user(UserRole::INTERVIEWER, 'interviewer@example.com');

        $this->actingAs($hr)->get(route('hr.assessments.show', $assessment))->assertOk();
        $this->actingAs($candidate)->get(route('hr.assessments.show', $assessment))->assertForbidden();
        $this->actingAs($interviewer)->get(route('hr.assessments.index', $job))->assertForbidden();
    }
}
