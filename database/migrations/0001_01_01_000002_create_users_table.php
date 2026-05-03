<?php

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id('user_id');
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id')->nullOnDelete();
            $table->string('name', 160);
            $table->string('email', 180)->unique();
            $table->string('password_hash');
            $table->string('role', 32)->index()->default(UserRole::CANDIDATE->value);
            $table->string('status', 32)->index()->default(AccountStatus::ACTIVE->value);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
