<?php

namespace App\Http\Requests\Candidate;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::CANDIDATE) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'max:40'],
            'current_title' => ['required', 'string', 'max:160'],
            'years_experience' => ['required', 'integer', 'min:0', 'max:60'],
            'location' => ['required', 'string', 'max:160'],
            'resume_url' => ['required', 'string', 'max:2048'],
            'skill_keywords' => ['required', 'string', 'regex:/[^,\s]+/'],
        ];
    }
}
