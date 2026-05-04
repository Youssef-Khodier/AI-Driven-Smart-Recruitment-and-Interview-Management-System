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

class JobRequisitionLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_hr_can_move_requisition_through_lifecycle_with_different_approver(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $creator = $this->user(UserRole::HR_ADMIN, 'creator@example.com');
        $approver = $this->user(UserRole::HR_ADMIN, 'approver@example.com');

        $response = $this->actingAs($creator)->post(route('hr.requisitions.store'), [
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Build hiring workflows.',
            'requirements' => 'Laravel PHP and MySQL.',
        ]);

        $requisition = JobRequisition::firstOrFail();
        $response->assertRedirect(route('hr.requisitions.show', $requisition));
        $this->assertSame(JobRequisitionStatus::DRAFT, $requisition->status);

        $this->actingAs($creator)->post(route('hr.requisitions.submit', $requisition))->assertRedirect();
        $this->assertSame(JobRequisitionStatus::PENDING_APPROVAL, $requisition->refresh()->status);

        $this->actingAs($creator)->post(route('hr.requisitions.approve', $requisition))
            ->assertSessionHasErrors('approval');

        $this->actingAs($approver)->post(route('hr.requisitions.approve', $requisition))->assertRedirect();
        $this->assertSame(JobRequisitionStatus::APPROVED, $requisition->refresh()->status);
        $this->assertSame($approver->user_id, $requisition->approved_by);

        $this->actingAs($approver)->post(route('hr.requisitions.open', $requisition))->assertRedirect();
        $this->assertSame(JobRequisitionStatus::OPEN, $requisition->refresh()->status);

        $this->actingAs($approver)->post(route('hr.requisitions.close', $requisition))->assertRedirect();
        $this->assertSame(JobRequisitionStatus::CLOSED, $requisition->refresh()->status);
    }

    private function user(UserRole $role, string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => $role,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
