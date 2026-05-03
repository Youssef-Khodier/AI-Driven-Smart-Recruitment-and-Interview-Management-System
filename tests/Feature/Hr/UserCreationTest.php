<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\AccountAuditRecord;
use App\Models\Candidate;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_create_interviewer_account(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);
        $department = Department::create(['name' => 'Engineering']);

        $this->actingAs($admin)->post('/hr/users', [
            'name' => 'Interviewer One',
            'email' => 'interviewer@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::INTERVIEWER->value,
            'status' => AccountStatus::ACTIVE->value,
            'department_id' => $department->department_id,
        ])->assertRedirect(route('hr.users.index'));

        $target = User::where('email', 'interviewer@example.com')->firstOrFail();
        $this->assertSame(UserRole::INTERVIEWER, $target->role);
        $this->assertSame($department->department_id, $target->department_id);
        $this->assertTrue(AccountAuditRecord::where('action', AuditAction::USER_CREATED->value)->where('target_user_id', $target->user_id)->exists());
    }

    public function test_hr_admin_can_create_candidate_with_phone(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);

        $this->actingAs($admin)->post('/hr/users', [
            'name' => 'Candidate One',
            'email' => 'candidate@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::CANDIDATE->value,
            'status' => AccountStatus::ACTIVE->value,
            'phone' => '+15551234567',
        ])->assertRedirect(route('hr.users.index'));

        $target = User::where('email', 'candidate@example.com')->firstOrFail();
        $this->assertTrue(Candidate::where('candidate_id', $target->user_id)->where('phone', '+15551234567')->exists());
    }

    public function test_hr_creation_validates_duplicate_email_invalid_role_and_candidate_phone(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);
        $this->user(UserRole::INTERVIEWER, AccountStatus::ACTIVE, 'existing@example.com');

        $this->actingAs($admin)->from('/hr/users/create')->post('/hr/users', [
            'name' => 'Candidate One',
            'email' => 'existing@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'INVALID',
            'status' => AccountStatus::ACTIVE->value,
        ])->assertRedirect('/hr/users/create')->assertSessionHasErrors(['email', 'role']);

        $this->actingAs($admin)->from('/hr/users/create')->post('/hr/users', [
            'name' => 'Candidate One',
            'email' => 'candidate@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => UserRole::CANDIDATE->value,
            'status' => AccountStatus::ACTIVE->value,
        ])->assertRedirect('/hr/users/create')->assertSessionHasErrors('phone');
    }

    private function user(UserRole $role, AccountStatus $status = AccountStatus::ACTIVE, ?string $email = null): User
    {
        return User::create([
            'name' => $role->value.' User',
            'email' => $email ?? strtolower($role->value).uniqid().'@example.com',
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => $status,
        ]);
    }
}
