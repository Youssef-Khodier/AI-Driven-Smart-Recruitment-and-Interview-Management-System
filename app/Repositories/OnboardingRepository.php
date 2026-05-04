<?php

namespace App\Repositories;

use App\Core\Database;

final class OnboardingRepository
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
}
