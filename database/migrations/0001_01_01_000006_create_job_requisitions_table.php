<?php

use App\Enums\JobRequisitionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requisitions', function (Blueprint $table): void {
            $table->id('job_id');
            $table->foreignId('department_id')->constrained('departments', 'department_id')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('description');
            $table->text('requirements');
            $table->string('status', 40)->index()->default(JobRequisitionStatus::DRAFT->value);
            $table->foreignId('created_by')->constrained('users', 'user_id')->restrictOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requisitions');
    }
};
