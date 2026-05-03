<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class HrAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_hr_users_cannot_access_hr_user_administration(): void
    {
        $candidate = $this->user(UserRole::CANDIDATE);
        $interviewer = $this->user(UserRole::INTERVIEWER);

        $this->actingAs($candidate)->get('/hr/users')->assertForbidden();
        $this->actingAs($interviewer)->get('/hr/users/create')->assertForbidden();
        $this->actingAs($candidate)->post('/hr/users', [])->assertForbidden();
    }

    private function user(UserRole $role): User
    {
        return User::create([
            'name' => $role->value.' User',
            'email' => strtolower($role->value).uniqid().'@example.com',
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
