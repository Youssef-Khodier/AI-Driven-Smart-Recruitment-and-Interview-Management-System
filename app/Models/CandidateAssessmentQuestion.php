<?php

namespace App\Models;

use App\Enums\AssessmentQuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CandidateAssessmentQuestion extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $primaryKey = 'attempt_question_id';

    protected $fillable = [
        'ca_id',
        'question_id',
        'display_order',
        'question_type',
        'question_text',
        'options',
        'correct_answer',
        'points',
    ];

    protected function casts(): array
    {
        return [
            'question_type' => AssessmentQuestionType::class,
            'options' => 'array',
            'points' => 'decimal:2',
        ];
    }

    public function candidateAssessment(): BelongsTo
    {
        return $this->belongsTo(CandidateAssessment::class, 'ca_id', 'ca_id');
    }

    public function sourceQuestion(): BelongsTo
    {
        return $this->belongsTo(Question::class, 'question_id', 'question_id');
    }

    public function submission(): HasOne
    {
        return $this->hasOne(Submission::class, 'attempt_question_id', 'attempt_question_id');
    }
}
