<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Submission extends Model
{
    use HasFactory;

    protected $primaryKey = 'submission_id';

    protected $fillable = [
        'ca_id',
        'attempt_question_id',
        'question_id',
        'answer_text',
        'saved_at',
        'finalized_at',
        'is_correct',
        'awarded_points',
    ];

    protected function casts(): array
    {
        return [
            'saved_at' => 'datetime',
            'finalized_at' => 'datetime',
            'is_correct' => 'boolean',
            'awarded_points' => 'decimal:2',
        ];
    }

    public function candidateAssessment(): BelongsTo
    {
        return $this->belongsTo(CandidateAssessment::class, 'ca_id', 'ca_id');
    }

    public function attemptQuestion(): BelongsTo
    {
        return $this->belongsTo(CandidateAssessmentQuestion::class, 'attempt_question_id', 'attempt_question_id');
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }
}
