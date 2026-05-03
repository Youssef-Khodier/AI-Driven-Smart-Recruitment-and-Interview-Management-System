<?php

namespace Database\Seeders;

use App\Models\Department;
use Illuminate\Database\Seeder;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        Department::firstOrCreate(
            ['name' => 'Human Resources'],
            ['description' => 'Owns recruitment operations and account administration.']
        );

        Department::firstOrCreate(
            ['name' => 'Engineering'],
            ['description' => 'Provides technical interviewers for candidate evaluations.']
        );
    }
}
