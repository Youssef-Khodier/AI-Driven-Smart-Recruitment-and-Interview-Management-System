<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    use HasFactory;

    protected $primaryKey = 'application_id';

    protected $fillable = [
        'candidate_id',
        'job_id',
        'status',
        'match_score',
        'applied_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => ApplicationStatus::class,
            'match_score' => 'integer',
            'applied_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'candidate_id');
    }

    public function jobRequisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_id', 'job_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(ApplicationStatusHistory::class, 'application_id', 'application_id');
    }

    public function candidateAssessments(): HasMany
    {
        return $this->hasMany(CandidateAssessment::class, 'application_id', 'application_id');
    }
}
