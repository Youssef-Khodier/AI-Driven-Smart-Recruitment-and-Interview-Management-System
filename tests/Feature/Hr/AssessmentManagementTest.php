<?php

namespace Tests\Feature\Hr;

use App\Enums\AssessmentQuestionType;
use App\Enums\AssessmentType;
use App\Models\Assessment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentManagementTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_hr_can_create_assessment_and_question_for_job(): void
    {
        [$hr, , $job] = $this->assessmentFixture();

        $this->actingAs($hr)->post(route('hr.assessments.store', $job), [
            'title' => 'Backend Assessment',
            'type' => AssessmentType::TECHNICAL->value,
            'duration_minutes' => 45,
            'description' => 'Answer carefully.',
            'is_active' => '1',
        ])->assertRedirect();

        $assessment = Assessment::where('title', 'Backend Assessment')->firstOrFail();

        $this->actingAs($hr)->post(route('hr.assessment-questions.store', $assessment), [
            'type' => AssessmentQuestionType::MCQ->value,
            'difficulty_level' => 'MEDIUM',
            'question_text' => 'Pick PHP.',
            'options_text' => "PHP\nRuby",
            'correct_answer' => 'PHP',
            'points' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('hr.assessments.show', $assessment));

        $this->assertDatabaseHas('questions', ['assessment_id' => $assessment->assessment_id, 'question_text' => 'Pick PHP.']);
    }
}
