<?php

namespace App\Models;

use App\Enums\AssessmentAttemptStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CandidateAssessment extends Model
{
    use HasFactory;

    protected $primaryKey = 'ca_id';

    protected $fillable = [
        'application_id',
        'candidate_id',
        'assessment_id',
        'start_time',
        'end_time',
        'expires_at',
        'status',
        'score',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime',
            'end_time' => 'datetime',
            'expires_at' => 'datetime',
            'status' => AssessmentAttemptStatus::class,
            'score' => 'decimal:3',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class, 'candidate_id', 'candidate_id');
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id');
    }

    public function attemptQuestions(): HasMany
    {
        return $this->hasMany(CandidateAssessmentQuestion::class, 'ca_id', 'ca_id')->orderBy('display_order');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class, 'ca_id', 'ca_id');
    }

    public function integrityEvents(): HasMany
    {
        return $this->hasMany(AssessmentIntegrityEvent::class, 'ca_id', 'ca_id')->orderBy('occurred_at');
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [AssessmentAttemptStatus::SUBMITTED, AssessmentAttemptStatus::EXPIRED], true);
    }
}
