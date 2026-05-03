<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CandidateLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_active_candidate_can_login_and_reach_dashboard(): void
    {
        $candidate = $this->candidate();

        $this->post('/login', [
            'email' => $candidate->email,
            'password' => 'password',
        ])->assertRedirect(route('candidate.dashboard'));

        $this->assertAuthenticatedAs($candidate);
        $this->get('/candidate/dashboard')->assertOk()->assertSee('Candidate Dashboard');
    }

    public function test_invalid_candidate_credentials_are_safe(): void
    {
        $candidate = $this->candidate();

        $this->from('/login')->post('/login', [
            'email' => $candidate->email,
            'password' => 'wrong-password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    private function candidate(): User
    {
        return User::create([
            'name' => 'Candidate User',
            'email' => 'candidate@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
