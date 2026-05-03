<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_audit_records', function (Blueprint $table): void {
            $table->id('audit_id');
            $table->foreignId('actor_user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->foreignId('target_user_id')->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('action', 48);
            $table->json('old_values')->nullable();
            $table->json('new_values');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_audit_records');
    }
};
