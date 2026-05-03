<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_candidate_can_register_with_phone(): void
    {
        $this->post('/register', [
            'name' => 'New Candidate',
            'email' => 'candidate@example.com',
            'phone' => '+15551234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect(route('candidate.dashboard'));

        $user = User::where('email', 'candidate@example.com')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame(UserRole::CANDIDATE, $user->role);
        $this->assertTrue(Candidate::where('candidate_id', $user->user_id)->where('phone', '+15551234567')->exists());
    }

    public function test_registration_rejects_duplicate_email(): void
    {
        User::create([
            'name' => 'Existing',
            'email' => 'candidate@example.com',
            'password_hash' => bcrypt('password'),
            'role' => UserRole::CANDIDATE,
        ]);

        $this->from('/register')->post('/register', [
            'name' => 'New Candidate',
            'email' => 'candidate@example.com',
            'phone' => '+15551234567',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertRedirect('/register')->assertSessionHasErrors('email');
    }

    public function test_registration_requires_phone_and_confirmed_password(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'New Candidate',
            'email' => 'new@example.com',
            'password' => 'password',
            'password_confirmation' => 'different',
        ])->assertRedirect('/register')->assertSessionHasErrors(['phone', 'password']);
    }

    public function test_registration_rejects_submitted_role(): void
    {
        $this->from('/register')->post('/register', [
            'name' => 'New Candidate',
            'email' => 'new@example.com',
            'phone' => '+15551234567',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::HR_ADMIN->value,
        ])->assertRedirect('/register')->assertSessionHasErrors('role');
    }
}
