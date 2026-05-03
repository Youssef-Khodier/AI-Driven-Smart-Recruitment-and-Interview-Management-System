<?php

namespace Tests\Feature\Foundation;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeders_create_departments_and_first_active_hr_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(Department::where('name', 'Human Resources')->exists());
        $this->assertTrue(Department::where('name', 'Engineering')->exists());

        $admin = User::where('email', 'hr.admin@example.com')->firstOrFail();
        $this->assertSame(UserRole::HR_ADMIN, $admin->role);
        $this->assertSame(AccountStatus::ACTIVE, $admin->status);
    }
}
