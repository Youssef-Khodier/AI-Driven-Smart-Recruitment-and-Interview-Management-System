<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Candidate extends Model
{
    use HasFactory;

    protected $primaryKey = 'candidate_id';

    public $incrementing = false;

    protected $fillable = [
        'candidate_id',
        'phone',
        'current_title',
        'years_experience',
        'location',
        'resume_url',
        'skill_keywords',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'candidate_id', 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'candidate_id', 'candidate_id');
    }

    public function candidateAssessments(): HasMany
    {
        return $this->hasMany(CandidateAssessment::class, 'candidate_id', 'candidate_id');
    }
}
