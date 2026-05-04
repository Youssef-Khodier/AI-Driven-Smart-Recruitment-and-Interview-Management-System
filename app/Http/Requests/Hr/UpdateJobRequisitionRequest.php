<?php

namespace App\Http\Requests\Hr;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJobRequisitionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::HR_ADMIN) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'department_id' => ['required', 'integer', 'exists:departments,department_id'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string'],
            'requirements' => ['required', 'string'],
            'last_seen_updated_at' => ['required', 'date'],
        ];
    }
}
