<?php

namespace App\Http\Requests\Hr;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::HR_ADMIN) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:160'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', Rule::enum(UserRole::class)],
            'status' => ['required', Rule::enum(AccountStatus::class)],
            'department_id' => ['nullable', 'integer', 'exists:departments,department_id'],
            'phone' => ['nullable', 'required_if:role,'.UserRole::CANDIDATE->value, 'string', 'max:40'],
        ];
    }
}
