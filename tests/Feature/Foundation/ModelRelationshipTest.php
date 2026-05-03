<?php

namespace Tests\Feature\Foundation;

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

class ModelRelationshipTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_model_relationships_are_available(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $user = User::create([
            'department_id' => $department->department_id,
            'name' => 'Candidate One',
            'email' => 'candidate@example.com',
            'password_hash' => Hash::make('password'),
            'role' => UserRole::CANDIDATE,
            'status' => AccountStatus::ACTIVE,
        ]);
        $candidate = Candidate::create(['candidate_id' => $user->user_id, 'phone' => '+15551234567']);
        $audit = AccountAuditRecord::create([
            'actor_user_id' => $user->user_id,
            'target_user_id' => $user->user_id,
            'action' => AuditAction::USER_CREATED,
            'old_values' => null,
            'new_values' => ['role' => UserRole::CANDIDATE->value],
        ]);

        $this->assertTrue($user->department->is($department));
        $this->assertTrue($department->users->first()->is($user));
        $this->assertTrue($user->candidate->is($candidate));
        $this->assertTrue($candidate->user->is($user));
        $this->assertTrue($audit->actor->is($user));
        $this->assertTrue($audit->target->is($user));
    }
}
