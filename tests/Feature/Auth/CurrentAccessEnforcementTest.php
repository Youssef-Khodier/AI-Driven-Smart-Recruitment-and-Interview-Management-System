<?php

namespace Tests\Feature\Auth;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CurrentAccessEnforcementTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_changes_apply_on_next_protected_action(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN, 'admin@example.com');
        $target = $this->user(UserRole::INTERVIEWER, 'interviewer@example.com');

        $this->actingAs($admin)->put("/hr/users/{$target->user_id}/access", [
            'role' => UserRole::CANDIDATE->value,
            'status' => AccountStatus::ACTIVE->value,
        ])->assertRedirect(route('hr.users.index'));

        $this->actingAs($target)->get('/interviewer/dashboard')->assertForbidden();
        $this->actingAs($target)->get('/dashboard')->assertRedirect(route('candidate.dashboard'));
    }

    public function test_status_changes_apply_on_next_protected_action(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN, 'admin@example.com');
        $target = $this->user(UserRole::INTERVIEWER, 'interviewer@example.com');

        $this->actingAs($admin)->put("/hr/users/{$target->user_id}/access", [
            'role' => UserRole::INTERVIEWER->value,
            'status' => AccountStatus::INACTIVE->value,
        ])->assertRedirect(route('hr.users.index'));

        $this->actingAs($target)->get('/interviewer/dashboard')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function user(UserRole $role, string $email): User
    {
        return User::create([
            'name' => $role->value.' User',
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
