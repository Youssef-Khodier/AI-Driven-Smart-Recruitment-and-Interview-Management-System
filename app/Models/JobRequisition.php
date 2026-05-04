<?php

namespace App\Models;

use App\Enums\JobRequisitionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JobRequisition extends Model
{
    use HasFactory;

    protected $primaryKey = 'job_id';

    protected $fillable = [
        'department_id',
        'title',
        'description',
        'requirements',
        'status',
        'created_by',
        'approved_by',
        'approved_at',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => JobRequisitionStatus::class,
            'approved_at' => 'datetime',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'user_id');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class, 'job_id', 'job_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class, 'job_id', 'job_id');
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(JobRequisitionStatusHistory::class, 'job_id', 'job_id');
    }
}
