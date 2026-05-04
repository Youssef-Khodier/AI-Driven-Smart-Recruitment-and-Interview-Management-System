<?php

namespace App\Models;

use App\Enums\JobRequisitionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JobRequisitionStatusHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $primaryKey = 'history_id';

    protected $fillable = [
        'job_id',
        'actor_user_id',
        'old_status',
        'new_status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => JobRequisitionStatus::class,
            'new_status' => JobRequisitionStatus::class,
        ];
    }

    public function jobRequisition(): BelongsTo
    {
        return $this->belongsTo(JobRequisition::class, 'job_id', 'job_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'user_id');
    }
}
