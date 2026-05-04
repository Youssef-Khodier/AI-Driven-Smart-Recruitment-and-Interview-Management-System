<?php

namespace Tests\Feature\Candidate;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateProfileSkillKeywordsTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_profile_requires_skill_keywords_and_application_fields(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)->put(route('candidate.profile.update'), [])
            ->assertSessionHasErrors(['phone', 'current_title', 'years_experience', 'location', 'resume_url', 'skill_keywords']);
    }

    public function test_candidate_can_save_skill_keywords(): void
    {
        $candidate = $this->candidate();

        $this->actingAs($candidate)->put(route('candidate.profile.update'), [
            'phone' => '+15551234567',
            'current_title' => 'Laravel Developer',
            'years_experience' => 4,
            'location' => 'Remote',
            'resume_url' => 'https://example.com/resume.pdf',
            'skill_keywords' => 'Laravel, PHP, MySQL',
        ])->assertRedirect(route('candidate.profile'));

        $this->assertSame('Laravel, PHP, MySQL', $candidate->candidate->refresh()->skill_keywords);
    }

    private function candidate(): User
    {
        $user = User::create([
            'name' => 'Candidate',
            'email' => 'candidate@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => AccountStatus::ACTIVE,
        ]);
        Candidate::create(['candidate_id' => $user->user_id, 'phone' => '+15550000000']);

        return $user->load('candidate');
    }
}
