<?php

namespace App\Http\Requests\Hr;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserAccessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::HR_ADMIN) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['required', Rule::enum(AccountStatus::class)],
        ];
    }
}
