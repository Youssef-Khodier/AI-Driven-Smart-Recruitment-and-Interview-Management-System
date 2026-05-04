<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_requisition_status_histories', function (Blueprint $table): void {
            $table->id('history_id');
            $table->foreignId('job_id')->constrained('job_requisitions', 'job_id')->restrictOnDelete();
            $table->foreignId('actor_user_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->string('old_status', 40)->nullable();
            $table->string('new_status', 40);
            $table->text('reason')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_requisition_status_histories');
    }
};
