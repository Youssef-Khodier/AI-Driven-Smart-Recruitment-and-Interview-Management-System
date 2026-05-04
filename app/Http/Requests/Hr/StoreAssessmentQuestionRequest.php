<?php

namespace App\Http\Requests\Hr;

use App\Enums\AssessmentQuestionType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreAssessmentQuestionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::HR_ADMIN) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(AssessmentQuestionType::class)],
            'difficulty_level' => ['required', Rule::in(['EASY', 'MEDIUM', 'HARD'])],
            'question_text' => ['required', 'string'],
            'options_text' => ['nullable', 'string'],
            'correct_answer' => ['nullable', 'string'],
            'points' => ['required', 'numeric', 'min:0.01', 'max:999.99'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->input('type') !== AssessmentQuestionType::MCQ->value) {
                return;
            }

            $options = $this->parsedOptions();
            if (count($options) < 2) {
                $validator->errors()->add('options_text', 'MCQ questions require at least two answer choices.');
            }

            if (! in_array($this->input('correct_answer'), $options, true)) {
                $validator->errors()->add('correct_answer', 'The correct answer must match one of the MCQ choices exactly.');
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public function parsedOptions(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->input('options_text')) ?: [])
            ->map(fn (string $option): string => trim($option))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
