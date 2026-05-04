<?php

use App\Enums\AssessmentAttemptStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_assessments', function (Blueprint $table): void {
            $table->id('ca_id');
            $table->foreignId('application_id')->constrained('applications', 'application_id')->cascadeOnDelete();
            $table->foreignId('candidate_id')->constrained('candidates', 'candidate_id')->cascadeOnDelete();
            $table->foreignId('assessment_id')->constrained('assessments', 'assessment_id')->cascadeOnDelete();
            $table->timestamp('start_time')->nullable();
            $table->timestamp('end_time')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->string('status', 40)->default(AssessmentAttemptStatus::IN_PROGRESS->value)->index();
            $table->decimal('score', 6, 3)->nullable();
            $table->timestamps();

            $table->unique(['candidate_id', 'assessment_id']);
            $table->index(['application_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_assessments');
    }
};
