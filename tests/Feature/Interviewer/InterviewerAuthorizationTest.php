<?php

namespace Tests\Feature\Interviewer;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InterviewerAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_interviewer_cannot_access_hr_or_candidate_routes(): void
    {
        $interviewer = $this->user(UserRole::INTERVIEWER);

        $this->actingAs($interviewer)->get('/hr/dashboard')->assertForbidden();
        $this->actingAs($interviewer)->get('/hr/users')->assertForbidden();
        $this->actingAs($interviewer)->get('/candidate/dashboard')->assertForbidden();
        $this->actingAs($interviewer)->get('/candidate/profile')->assertForbidden();
    }

    private function user(UserRole $role): User
    {
        return User::create([
            'name' => 'Interviewer One',
            'email' => 'interviewer@example.com',
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
