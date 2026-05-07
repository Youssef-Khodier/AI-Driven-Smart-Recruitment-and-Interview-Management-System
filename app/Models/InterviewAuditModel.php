<?php

namespace App\Models;

use App\Core\Database;

final class InterviewAuditModel
{
    public static function record(int $interviewId, int $actorUserId, string $action, array $changedFields, ?string $reason = null): void
    {
        $payload = $changedFields;
        if ($reason !== null) {
            $payload['reason'] = $reason;
        }

        Database::insert('interview_audit_records', [
            'interview_id' => $interviewId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'changed_fields' => json_encode($payload),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
