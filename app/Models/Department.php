<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $primaryKey = 'department_id';

    protected $fillable = [
        'name',
        'description',
        'parent_department_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_department_id', 'department_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_department_id', 'department_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'department_id', 'department_id');
    }

    public function jobRequisitions(): HasMany
    {
        return $this->hasMany(JobRequisition::class, 'department_id', 'department_id');
    }
}
