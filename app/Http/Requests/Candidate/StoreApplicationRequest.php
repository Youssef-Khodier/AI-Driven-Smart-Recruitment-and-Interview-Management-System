<?php

namespace App\Http\Requests\Candidate;

use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\JobRequisition;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreApplicationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(UserRole::CANDIDATE) && $this->user()->isActive();
    }

    public function rules(): array
    {
        return [];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            $candidate = $this->user()?->candidate;
            $requisition = $this->route('requisition');

            if (! $requisition instanceof JobRequisition || $requisition->status !== JobRequisitionStatus::OPEN) {
                $validator->errors()->add('requisition', 'This job is not open for applications.');
            }

            if (! $candidate || ! $candidate->current_title || $candidate->years_experience === null || ! $candidate->location || ! $candidate->resume_url || ! $candidate->skill_keywords) {
                $validator->errors()->add('profile', 'Complete your candidate profile before applying.');
            }

        }];
    }
}
