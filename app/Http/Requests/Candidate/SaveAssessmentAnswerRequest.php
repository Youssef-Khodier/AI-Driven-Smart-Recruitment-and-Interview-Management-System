<?php

namespace App\Http\Requests\Candidate;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class SaveAssessmentAnswerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::CANDIDATE) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'answer_text' => ['nullable', 'string'],
        ];
    }
}
