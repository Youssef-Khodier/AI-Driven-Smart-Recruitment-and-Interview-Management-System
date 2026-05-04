<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_integrity_events', function (Blueprint $table): void {
            $table->id('event_id');
            $table->foreignId('ca_id')->constrained('candidate_assessments', 'ca_id')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->timestamp('occurred_at')->index();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['ca_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_integrity_events');
    }
};
