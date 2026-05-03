<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\AuditAction;
use App\Enums\UserRole;
use App\Models\AccountAuditRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAccessUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_admin_can_change_role_and_status_with_audit_records(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);
        $target = $this->user(UserRole::INTERVIEWER);

        $this->actingAs($admin)->put("/hr/users/{$target->user_id}/access", [
            'role' => UserRole::CANDIDATE->value,
            'status' => AccountStatus::INACTIVE->value,
        ])->assertRedirect(route('hr.users.index'));

        $target->refresh();
        $this->assertSame(UserRole::CANDIDATE, $target->role);
        $this->assertSame(AccountStatus::INACTIVE, $target->status);
        $this->assertTrue(AccountAuditRecord::where('action', AuditAction::ROLE_CHANGED->value)->where('target_user_id', $target->user_id)->exists());
        $this->assertTrue(AccountAuditRecord::where('action', AuditAction::STATUS_CHANGED->value)->where('target_user_id', $target->user_id)->exists());
    }

    public function test_last_active_hr_admin_cannot_be_downgraded_or_deactivated(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);

        $this->actingAs($admin)->from("/hr/users/{$admin->user_id}/access")->put("/hr/users/{$admin->user_id}/access", [
            'role' => UserRole::INTERVIEWER->value,
            'status' => AccountStatus::ACTIVE->value,
        ])->assertRedirect("/hr/users/{$admin->user_id}/access")->assertSessionHasErrors('role');

        $this->actingAs($admin)->from("/hr/users/{$admin->user_id}/access")->put("/hr/users/{$admin->user_id}/access", [
            'role' => UserRole::HR_ADMIN->value,
            'status' => AccountStatus::INACTIVE->value,
        ])->assertRedirect("/hr/users/{$admin->user_id}/access")->assertSessionHasErrors('role');
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
