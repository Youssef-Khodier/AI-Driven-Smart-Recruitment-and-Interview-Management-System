<?php

namespace App\Models;

use App\Core\Database;

final class PostOfferAuditModel
{
    public static function record(int $applicationId, ?int $offerId, ?int $onboardingId, int $actorUserId, string $action, array $changedFields): void
    {
        Database::insert('post_offer_audit_records', [
            'application_id' => $applicationId,
            'offer_id' => $offerId,
            'onboarding_id' => $onboardingId,
            'actor_user_id' => $actorUserId,
            'action' => $action,
            'changed_fields' => json_encode($changedFields),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
