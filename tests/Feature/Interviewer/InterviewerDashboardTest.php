<?php

namespace Tests\Feature\Interviewer;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class InterviewerDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_interviewer_can_login_and_access_dashboard(): void
    {
        $interviewer = $this->user(UserRole::INTERVIEWER);

        $this->post('/login', [
            'email' => $interviewer->email,
            'password' => 'password',
        ])->assertRedirect(route('interviewer.dashboard'));

        $this->get('/interviewer/dashboard')->assertOk()->assertSee('Interviewer Dashboard');
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
