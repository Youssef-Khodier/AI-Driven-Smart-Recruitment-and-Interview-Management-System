<?php

namespace App\Models;

use App\Enums\AssessmentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assessment extends Model
{
    use HasFactory;

    protected $primaryKey = 'assessment_id';

    protected $fillable = [
        'job_id',
        'title',
        'description',
        'type',
        'duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssessmentType::class,
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function jobRequisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_id', 'job_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(Question::class, 'assessment_id', 'assessment_id');
    }

    public function activeQuestions(): HasMany
    {
        return $this->questions()->where('is_active', true);
    }

    public function candidateAssessments(): HasMany
    {
        return $this->hasMany(CandidateAssessment::class, 'assessment_id', 'assessment_id');
    }
}
