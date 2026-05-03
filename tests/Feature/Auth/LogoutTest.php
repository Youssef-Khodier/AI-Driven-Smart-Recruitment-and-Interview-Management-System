<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_logout_and_protected_pages_require_login(): void
    {
        $candidate = $this->user(UserRole::CANDIDATE);

        $this->actingAs($candidate)->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
        $this->get('/candidate/dashboard')->assertRedirect(route('login'));
    }

    private function user(UserRole $role): User
    {
        return User::create([
            'name' => 'Candidate One',
            'email' => 'candidate@example.com',
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
