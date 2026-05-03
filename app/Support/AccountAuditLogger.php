<?php

namespace App\Support;

use App\Enums\AuditAction;
use App\Models\AccountAuditRecord;
use App\Models\User;

class AccountAuditLogger
{
    public function created(User $actor, User $target): AccountAuditRecord
    {
        return $this->write($actor, $target, AuditAction::USER_CREATED, null, [
            'name' => $target->name,
            'email' => $target->email,
            'role' => $target->role->value,
            'status' => $target->status->value,
            'department_id' => $target->department_id,
        ]);
    }

    public function roleChanged(User $actor, User $target, string $oldRole, string $newRole): AccountAuditRecord
    {
        return $this->write($actor, $target, AuditAction::ROLE_CHANGED, ['role' => $oldRole], ['role' => $newRole]);
    }

    public function statusChanged(User $actor, User $target, string $oldStatus, string $newStatus): AccountAuditRecord
    {
        return $this->write($actor, $target, AuditAction::STATUS_CHANGED, ['status' => $oldStatus], ['status' => $newStatus]);
    }

    private function write(User $actor, User $target, AuditAction $action, ?array $oldValues, array $newValues): AccountAuditRecord
    {
        return AccountAuditRecord::create([
            'actor_user_id' => $actor->user_id,
            'target_user_id' => $target->user_id,
            'action' => $action,
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);
    }
}
