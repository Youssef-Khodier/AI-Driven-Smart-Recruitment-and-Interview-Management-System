<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('candidates', function (Blueprint $table): void {
            $table->unsignedBigInteger('candidate_id')->primary();
            $table->string('phone', 40);
            $table->string('current_title', 160)->nullable();
            $table->unsignedTinyInteger('years_experience')->default(0);
            $table->string('location', 160)->nullable();
            $table->string('resume_url', 2048)->nullable();
            $table->timestamps();

            $table->foreign('candidate_id')->references('user_id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('candidates');
    }
};
