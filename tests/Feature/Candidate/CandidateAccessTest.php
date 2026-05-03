<?php

namespace Tests\Feature\Candidate;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_cannot_access_hr_or_interviewer_dashboards(): void
    {
        $candidate = $this->candidate('candidate@example.com');

        $this->actingAs($candidate)->get('/hr/dashboard')->assertForbidden();
        $this->actingAs($candidate)->get('/interviewer/dashboard')->assertForbidden();
    }

    public function test_candidate_profile_only_shows_authenticated_candidate(): void
    {
        $candidate = $this->candidate('candidate@example.com', '+15551230000');
        $other = $this->candidate('other@example.com', '+15559990000');

        $this->actingAs($candidate)
            ->get('/candidate/profile')
            ->assertOk()
            ->assertSee($candidate->email)
            ->assertSee('+15551230000')
            ->assertDontSee($other->email)
            ->assertDontSee('+15559990000');

        $this->actingAs($candidate)->get('/candidate/profile/'.$other->user_id)->assertNotFound();
    }

    private function candidate(string $email, string $phone = '+15551234567'): User
    {
        $user = User::create([
            'name' => 'Candidate User',
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => AccountStatus::ACTIVE,
        ]);

        Candidate::create(['candidate_id' => $user->user_id, 'phone' => $phone]);

        return $user;
    }
}
