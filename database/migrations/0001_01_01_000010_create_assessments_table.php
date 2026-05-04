<?php

use App\Enums\AssessmentType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table): void {
            $table->id('assessment_id');
            $table->foreignId('job_id')->constrained('job_requisitions', 'job_id')->restrictOnDelete();
            $table->string('title', 180);
            $table->text('description')->nullable();
            $table->string('type', 40)->default(AssessmentType::TECHNICAL->value);
            $table->unsignedInteger('duration_minutes');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['job_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
