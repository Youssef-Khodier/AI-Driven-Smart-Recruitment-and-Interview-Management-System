<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class FirstHrAdminSeeder extends Seeder
{
    public function run(): void
    {
        $department = Department::firstOrCreate(['name' => 'Human Resources']);

        User::updateOrCreate(
            ['email' => env('FIRST_HR_ADMIN_EMAIL', 'hr.admin@example.com')],
            [
                'department_id' => $department->department_id,
                'name' => env('FIRST_HR_ADMIN_NAME', 'SRIM HR Admin'),
                'password_hash' => Hash::make(env('FIRST_HR_ADMIN_PASSWORD', 'password')),
                'role' => UserRole::HR_ADMIN,
                'status' => AccountStatus::ACTIVE,
            ]
        );
    }
}
