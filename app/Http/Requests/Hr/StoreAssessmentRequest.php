<?php

namespace App\Http\Requests\Hr;

use App\Enums\AssessmentType;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAssessmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::HR_ADMIN) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:180'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::enum(AssessmentType::class)],
            'duration_minutes' => ['required', 'integer', 'min:1', 'max:480'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
