<?php

namespace App\Http\Controllers\Hr;

use App\Enums\AssessmentQuestionType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Hr\StoreAssessmentQuestionRequest;
use App\Http\Requests\Hr\UpdateAssessmentQuestionRequest;
use App\Models\Assessment;
use App\Models\Question;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

class AssessmentQuestionController extends Controller
{
    public function create(Assessment $assessment): View
    {
        Gate::authorize('update', $assessment);

        return view('hr.assessment-questions.create', [
            'title' => 'Add Question',
            'assessment' => $assessment,
            'question' => new Question(['type' => AssessmentQuestionType::MCQ, 'difficulty_level' => 'MEDIUM', 'points' => 1, 'is_active' => true]),
            'types' => AssessmentQuestionType::cases(),
        ]);
    }

    public function store(StoreAssessmentQuestionRequest $request, Assessment $assessment): RedirectResponse
    {
        Gate::authorize('update', $assessment);
        $assessment->questions()->create($this->payload($request));

        return redirect()->route('hr.assessments.show', $assessment)
            ->with('status', 'Question added.');
    }

    public function edit(Question $question): View
    {
        Gate::authorize('update', $question->assessment);

        return view('hr.assessment-questions.edit', [
            'title' => 'Edit Question',
            'assessment' => $question->assessment,
            'question' => $question,
            'types' => AssessmentQuestionType::cases(),
        ]);
    }

    public function update(UpdateAssessmentQuestionRequest $request, Question $question): RedirectResponse
    {
        Gate::authorize('update', $question->assessment);
        $question->update($this->payload($request));

        return redirect()->route('hr.assessments.show', $question->assessment)
            ->with('status', 'Question updated for future attempts. Existing attempts keep their snapshots.');
    }

    public function deactivate(Question $question): RedirectResponse
    {
        Gate::authorize('update', $question->assessment);
        $question->update(['is_active' => false]);

        return redirect()->route('hr.assessments.show', $question->assessment)
            ->with('status', 'Question deactivated for future attempts.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreAssessmentQuestionRequest $request): array
    {
        $validated = $request->validated();
        $type = AssessmentQuestionType::from($validated['type']);

        return [
            'type' => $type,
            'difficulty_level' => $validated['difficulty_level'],
            'question_text' => $validated['question_text'],
            'options' => $type === AssessmentQuestionType::MCQ ? $request->parsedOptions() : null,
            'correct_answer' => $validated['correct_answer'] ?? null,
            'points' => $validated['points'],
            'is_active' => $request->boolean('is_active', true),
        ];
    }
}
