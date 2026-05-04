<?php

namespace Tests\Feature\Hr;

use App\Enums\AccountStatus;
use App\Enums\JobRequisitionStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\JobRequisition;
use App\Models\JobRequisitionStatusHistory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class JobRequisitionStatusHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_transitions_create_history_records(): void
    {
        $department = Department::create(['name' => 'Engineering']);
        $creator = $this->hr('creator@example.com');
        $approver = $this->hr('approver@example.com');
        $requisition = JobRequisition::create([
            'department_id' => $department->department_id,
            'title' => 'Laravel Developer',
            'description' => 'Description',
            'requirements' => 'Requirements',
            'status' => JobRequisitionStatus::DRAFT,
            'created_by' => $creator->user_id,
        ]);

        $this->actingAs($creator)->post(route('hr.requisitions.submit', $requisition));
        $this->actingAs($approver)->post(route('hr.requisitions.approve', $requisition));
        $this->actingAs($approver)->post(route('hr.requisitions.open', $requisition));

        $this->assertCount(3, JobRequisitionStatusHistory::all());
        $this->assertDatabaseHas('job_requisition_status_histories', [
            'job_id' => $requisition->job_id,
            'actor_user_id' => $approver->user_id,
            'old_status' => JobRequisitionStatus::APPROVED->value,
            'new_status' => JobRequisitionStatus::OPEN->value,
        ]);
    }

    private function hr(string $email): User
    {
        return User::create([
            'name' => $email,
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role' => UserRole::HR_ADMIN,
            'status' => AccountStatus::ACTIVE,
        ]);
    }
}
