<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JobRequisitionValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_requisition_requires_core_fields(): void
    {
        $this->actingAs($this->hr())->post(route('hr.requisitions.store'), [])
            ->assertSessionHasErrors(['department_id', 'title', 'description', 'requirements']);
    }

    public function test_stale_edit_is_blocked(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $hr = $this->hr();
        $requisition = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Old Title',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => JobRequisitionStatus::DRAFT,
            'created_by' => $hr->user_id,
        ]);

        $this->actingAs($hr)->put(route('hr.requisitions.update', $requisition), [
            'department_id' => $department->department_id,
            'title' => 'New Title',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'last_seen_updated_at' => now()->subDay()->toIso8601String(),
        ])->assertSessionHasErrors('last_seen_updated_at');

        $this->assertSame('Old Title', $requisition->refresh()->title);
    }

    private function hr(): User
    {
        return User::create([
            'name' => 'HR Admin',
            'email' => fake()->unique()->safeEmail(),
            'password_hash' => Hash::make('password'),
            'role' => UserRole::HR_ADMIN,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
