<?php

namespace App\Models;

use App\Enums\ApplicationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApplicationStatusHistory extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $primaryKey = 'history_id';

    protected $fillable = [
        'application_id',
        'actor_user_id',
        'old_status',
        'new_status',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'old_status' => ApplicationStatus::class,
            'new_status' => ApplicationStatus::class,
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class, 'application_id', 'application_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id', 'user_id');
    }
}
