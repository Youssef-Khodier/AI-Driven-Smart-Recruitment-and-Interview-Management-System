<?php

namespace Tests\Feature\Foundation;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_middleware_denies_mismatched_user(): void
    {
        $candidate = $this->user(UserRole::CANDIDATE);

        $this->actingAs($candidate)->get('/hr/dashboard')->assertForbidden();
    }

    public function test_active_middleware_ends_inactive_user_access(): void
    {
        $candidate = $this->user(UserRole::CANDIDATE, AccountStatus::INACTIVE);

        $this->actingAs($candidate)
            ->get('/candidate/dashboard')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    private function user(UserRole $role, AccountStatus $status = AccountStatus::ACTIVE): User
    {
        return User::create([
            'name' => $role->value.' User',
            'email' => strtolower($role->value).uniqid().'@example.com',
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }
}
