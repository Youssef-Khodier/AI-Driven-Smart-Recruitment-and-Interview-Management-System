<?php

use App\Enums\ApplicationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('applications', function (Blueprint $table): void {
            $table->id('application_id');
            $table->foreignId('candidate_id')->constrained('candidates', 'candidate_id')->restrictOnDelete();
            $table->foreignId('job_id')->constrained('job_requisitions', 'job_id')->restrictOnDelete();
            $table->string('status', 40)->index()->default(ApplicationStatus::APPLIED->value);
            $table->unsignedTinyInteger('match_score');
            $table->timestamp('applied_at');
            $table->timestamps();

            $table->unique(['candidate_id', 'job_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('applications');
    }
};
