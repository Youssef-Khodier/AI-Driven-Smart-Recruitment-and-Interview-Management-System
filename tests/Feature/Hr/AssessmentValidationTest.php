<?php

namespace Tests\Feature\Hr;

use App\Enums\AssessmentQuestionType;
use App\Enums\AssessmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\AssessmentTestHelpers;
use Tests\TestCase;

class AssessmentValidationTest extends TestCase
{
    use RefreshDatabase;
    use AssessmentTestHelpers;

    public function test_assessment_and_question_forms_validate_required_inputs(): void
    {
        [$hr, , $job, , $assessment] = $this->assessmentFixture();

        $this->actingAs($hr)->post(route('hr.assessments.store', $job), [
            'title' => 'No',
            'type' => AssessmentType::TECHNICAL->value,
            'duration_minutes' => 0,
        ])->assertSessionHasErrors(['title', 'duration_minutes']);

        $this->actingAs($hr)->post(route('hr.assessment-questions.store', $assessment), [
            'type' => AssessmentQuestionType::MCQ->value,
            'difficulty_level' => 'EASY',
            'question_text' => 'Invalid MCQ',
            'options_text' => 'Only one',
            'correct_answer' => 'Missing',
            'points' => 1,
        ])->assertSessionHasErrors(['options_text', 'correct_answer']);
    }
}
