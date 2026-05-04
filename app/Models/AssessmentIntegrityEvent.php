<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentIntegrityEvent extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $primaryKey = 'event_id';

    protected $fillable = [
        'ca_id',
        'event_type',
        'occurred_at',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    public function candidateAssessment(): BelongsTo
    {
        return $this->belongsTo(CandidateAssessment::class, 'ca_id', 'ca_id');
    }
}
