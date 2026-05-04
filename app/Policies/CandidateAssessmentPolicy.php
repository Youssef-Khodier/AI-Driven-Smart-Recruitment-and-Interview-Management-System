<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CandidateAssessment;
use App\Models\User;

class CandidateAssessmentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isActive() ? null : false;
    }

    public function view(User $user, CandidateAssessment $candidateAssessment): bool
    {
        if ($user->hasRole(UserRole::HR_ADMIN)) {
            return true;
        }

        return $user->hasRole(UserRole::CANDIDATE)
            && $user->candidate?->candidate_id === $candidateAssessment->candidate_id;
    }

    public function update(User $user, CandidateAssessment $candidateAssessment): bool
    {
        return $user->hasRole(UserRole::CANDIDATE)
            && $user->candidate?->candidate_id === $candidateAssessment->candidate_id
            && ! $candidateAssessment->isTerminal();
    }

    public function submit(User $user, CandidateAssessment $candidateAssessment): bool
    {
        return $this->update($user, $candidateAssessment);
    }

    public function recordFocusEvent(User $user, CandidateAssessment $candidateAssessment): bool
    {
        return $this->update($user, $candidateAssessment);
    }

    public function review(User $user, CandidateAssessment $candidateAssessment): bool
    {
        return $user->hasRole(UserRole::HR_ADMIN);
    }
}
