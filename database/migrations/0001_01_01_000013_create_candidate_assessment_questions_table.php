<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidate_assessment_questions', function (Blueprint $table): void {
            $table->id('attempt_question_id');
            $table->foreignId('ca_id')->constrained('candidate_assessments', 'ca_id')->cascadeOnDelete();
            $table->foreignId('question_id')->nullable()->constrained('questions', 'question_id')->nullOnDelete();
            $table->unsignedInteger('display_order');
            $table->string('question_type', 40);
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->decimal('points', 6, 2);
            $table->timestamp('created_at')->nullable();

            $table->unique(['ca_id', 'display_order']);
            $table->unique(['ca_id', 'question_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidate_assessment_questions');
    }
};
