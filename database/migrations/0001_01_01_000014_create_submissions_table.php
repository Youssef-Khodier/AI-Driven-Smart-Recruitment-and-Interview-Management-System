<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table): void {
            $table->id('submission_id');
            $table->foreignId('ca_id')->constrained('candidate_assessments', 'ca_id')->cascadeOnDelete();
            $table->foreignId('attempt_question_id')->constrained('candidate_assessment_questions', 'attempt_question_id')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('questions', 'question_id')->nullOnDelete();
            $table->longText('answer_text')->nullable();
            $table->timestamp('saved_at')->nullable()->index();
            $table->timestamp('finalized_at')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->decimal('awarded_points', 6, 2)->nullable();
            $table->timestamps();

            $table->unique(['ca_id', 'attempt_question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
