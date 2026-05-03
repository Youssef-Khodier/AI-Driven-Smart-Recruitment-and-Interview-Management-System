<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InactiveAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_account_cannot_login(): void
    {
        $candidate = $this->user(AccountStatus::INACTIVE);

        $this->from('/login')->post('/login', [
            'email' => $candidate->email,
            'password' => 'password',
        ])->assertRedirect('/login')->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_inactive_session_is_terminated_on_next_protected_page(): void
    {
        $candidate = $this->user(AccountStatus::ACTIVE);
        $this->actingAs($candidate);
        $candidate->forceFill(['status' => AccountStatus::INACTIVE])->save();

        $this->get('/candidate/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function user(AccountStatus $status): User
    {
        return User::create([
            'name' => 'Candidate One',
            'email' => uniqid('candidate').'@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => $status,
        ]);
    }
}
