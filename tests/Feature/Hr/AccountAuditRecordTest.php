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

class AccountAuditRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_audit_records_capture_actor_target_old_and_new_values(): void
    {
        $admin = $this->user(UserRole::HR_ADMIN);
        $target = $this->user(UserRole::INTERVIEWER);

        $this->actingAs($admin)->put("/hr/users/{$target->user_id}/access", [
            'role' => UserRole::HR_ADMIN->value,
            'status' => AccountStatus::INACTIVE->value,
        ]);

        $roleAudit = AccountAuditRecord::where('action', AuditAction::ROLE_CHANGED->value)->firstOrFail();
        $this->assertSame($admin->user_id, $roleAudit->actor_user_id);
        $this->assertSame($target->user_id, $roleAudit->target_user_id);
        $this->assertSame(['role' => UserRole::INTERVIEWER->value], $roleAudit->old_values);
        $this->assertSame(['role' => UserRole::HR_ADMIN->value], $roleAudit->new_values);

        $statusAudit = AccountAuditRecord::where('action', AuditAction::STATUS_CHANGED->value)->firstOrFail();
        $this->assertSame(['status' => AccountStatus::ACTIVE->value], $statusAudit->old_values);
        $this->assertSame(['status' => AccountStatus::INACTIVE->value], $statusAudit->new_values);
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
