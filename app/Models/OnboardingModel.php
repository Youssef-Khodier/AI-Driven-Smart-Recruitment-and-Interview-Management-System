<?php

namespace App\Models;

use App\Core\Database;

final class OnboardingModel
{
    public static function getList(): array
    {
        return Database::fetchAll(
            'SELECT ob.*, o.application_id, o.offer_type, a.job_id, j.title as job_title, c.candidate_id, u.name as candidate_name 
             FROM onboarding ob
             JOIN offers o ON ob.offer_id = o.offer_id
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             ORDER BY ob.created_at DESC'
        );
    }

    public static function find(int $onboardingId): ?array
    {
        return Database::fetch(
            'SELECT ob.*, o.application_id, o.offer_type, a.job_id, j.title as job_title, c.candidate_id, u.name as candidate_name 
             FROM onboarding ob
             JOIN offers o ON ob.offer_id = o.offer_id
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             WHERE ob.onboarding_id = ?',
             [$onboardingId]
        );
    }

    public static function findByOfferId(int $offerId): ?array
    {
        return Database::fetch('SELECT * FROM onboarding WHERE offer_id = ?', [$offerId]);
    }

    public static function create(int $offerId, string $status, ?string $startDate, bool $documentsCompleted, int $createdBy): int
    {
        return Database::insert('onboarding', [
            'offer_id' => $offerId,
            'status' => $status,
            'start_date' => $startDate,
            'documents_completed' => (int)$documentsCompleted,
            'created_by' => $createdBy,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function update(int $onboardingId, string $status, ?string $startDate, bool $documentsCompleted): void
    {
        Database::update('onboarding', [
            'status' => $status,
            'start_date' => $startDate,
            'documents_completed' => (int)$documentsCompleted,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'onboarding_id = ?', [$onboardingId]);
    }

    public static function completedTaskKeys(int $onboardingId): array
    {
        $records = Database::fetchAll(
            "SELECT changed_fields
             FROM post_offer_audit_records
             WHERE onboarding_id = ? AND action = 'ONBOARDING_TASK_COMPLETE'
             ORDER BY created_at ASC, audit_id ASC",
            [$onboardingId]
        );

        $keys = [];
        foreach ($records as $record) {
            $payload = json_decode($record['changed_fields'] ?? '', true);
            $taskKey = $payload['task_key']['new'] ?? $payload['task_key'] ?? null;
            if (is_string($taskKey) && $taskKey !== '' && !in_array($taskKey, $keys, true)) {
                $keys[] = $taskKey;
            }
        }

        return $keys;
    }

    public static function completeTask(array $onboarding, string $taskKey, int $actorUserId): void
    {
        if (in_array($taskKey, self::completedTaskKeys((int)$onboarding['onboarding_id']), true)) {
            return;
        }

        PostOfferAuditModel::record(
            (int)$onboarding['application_id'],
            (int)$onboarding['offer_id'],
            (int)$onboarding['onboarding_id'],
            $actorUserId,
            'ONBOARDING_TASK_COMPLETE',
            ['task_key' => ['new' => $taskKey]]
        );
    }
}
