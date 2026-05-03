<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'department_id',
        'name',
        'email',
        'password_hash',
        'role',
        'status',
    ];

    protected $hidden = [
        'password_hash',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'role' => UserRole::class,
            'status' => AccountStatus::class,
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    public function getAuthPasswordName(): string
    {
        return 'password_hash';
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'department_id', 'department_id');
    }

    public function candidate(): HasOne
    {
        return $this->hasOne(Candidate::class, 'candidate_id', 'user_id');
    }

    public function performedAccountAudits(): HasMany
    {
        return $this->hasMany(AccountAuditRecord::class, 'actor_user_id', 'user_id');
    }

    public function targetAccountAudits(): HasMany
    {
        return $this->hasMany(AccountAuditRecord::class, 'target_user_id', 'user_id');
    }

    public function isActive(): bool
    {
        return $this->status === AccountStatus::ACTIVE;
    }

    public function hasRole(UserRole|string $role): bool
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;

        return $this->role->value === $roleValue;
    }
}
