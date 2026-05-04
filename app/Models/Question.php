<?php

namespace App\Models;

use App\Enums\AssessmentQuestionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Question extends Model
{
    use HasFactory;

    protected $primaryKey = 'question_id';

    protected $fillable = [
        'assessment_id',
        'type',
        'difficulty_level',
        'question_text',
        'options',
        'correct_answer',
        'points',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'type' => AssessmentQuestionType::class,
            'options' => 'array',
            'points' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class, 'assessment_id', 'assessment_id');
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(CandidateAssessmentQuestion::class, 'question_id', 'question_id');
    }
}
