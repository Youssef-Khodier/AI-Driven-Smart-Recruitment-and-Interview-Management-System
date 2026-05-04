<?php

namespace App\Repositories;

use App\Core\Database;

final class InterviewAuditRepository
{
    public static function record(int $interviewId, int $actorUserId, string $action, array $changedFields): void
    {
        Database::insert('interview_audit_records', [
            'interview_id' => $interviewId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'changed_fields' => json_encode($changedFields),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
