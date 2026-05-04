<?php

use App\Enums\AssessmentQuestionType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table): void {
            $table->id('question_id');
            $table->foreignId('assessment_id')->constrained('assessments', 'assessment_id')->cascadeOnDelete();
            $table->string('type', 40)->default(AssessmentQuestionType::MCQ->value);
            $table->string('difficulty_level', 20)->default('MEDIUM');
            $table->text('question_text');
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->decimal('points', 6, 2)->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['assessment_id', 'difficulty_level']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};
