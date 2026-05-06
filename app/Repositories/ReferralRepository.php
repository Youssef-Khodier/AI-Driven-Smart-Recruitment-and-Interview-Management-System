<?php

namespace App\Repositories;

use App\Core\Database;

/**
 * Repository for referral and referral reward tracking.
 */
final class ReferralRepository
{
    public static function create(array $data): int
    {
        return Database::insert('referrals', [
            'application_id' => $data['application_id'],
            'candidate_id' => $data['candidate_id'],
            'referrer_user_id' => $data['referrer_user_id'] ?? null,
            'referrer_name' => $data['referrer_name'] ?? null,
            'referrer_email' => $data['referrer_email'] ?? null,
            'referral_source' => $data['referral_source'] ?? 'INTERNAL',
            'notes' => $data['notes'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function findByApplication(int $applicationId): ?array
    {
        return Database::fetch(
            'SELECT r.*, u.name AS referrer_user_name
             FROM referrals r
             LEFT JOIN users u ON r.referrer_user_id = u.user_id
             WHERE r.application_id = ?',
            [$applicationId]
        ) ?: null;
    }

    public static function findById(int $referralId): ?array
    {
        return Database::fetch('SELECT * FROM referrals WHERE referral_id = ?', [$referralId]) ?: null;
    }

    public static function allWithDetails(): array
    {
        return Database::fetchAll(
            'SELECT r.*, u.name AS referrer_user_name, cu.name AS candidate_name,
                    j.title AS job_title, rw.reward_status, rw.reward_amount
             FROM referrals r
             LEFT JOIN users u ON r.referrer_user_id = u.user_id
             JOIN candidates c ON r.candidate_id = c.candidate_id
             JOIN users cu ON c.candidate_id = cu.user_id
             JOIN applications a ON r.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             LEFT JOIN referral_rewards rw ON rw.referral_id = r.referral_id
             ORDER BY r.created_at DESC'
        );
    }

    // --- Rewards ---
    public static function createReward(int $referralId, ?float $amount = null, string $rewardType = 'MONETARY'): int
    {
        return Database::insert('referral_rewards', [
            'referral_id' => $referralId,
            'reward_status' => 'PENDING',
            'reward_amount' => $amount,
            'reward_type' => $rewardType,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getReward(int $referralId): ?array
    {
        return Database::fetch(
            'SELECT * FROM referral_rewards WHERE referral_id = ?',
            [$referralId]
        ) ?: null;
    }

    public static function approveReward(int $rewardId, int $approvedBy): void
    {
        Database::update('referral_rewards', [
            'reward_status' => 'APPROVED',
            'approved_by' => $approvedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'reward_id = ?', [$rewardId]);
    }

    public static function markPaid(int $rewardId): void
    {
        Database::update('referral_rewards', [
            'reward_status' => 'PAID',
            'paid_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'reward_id = ?', [$rewardId]);
    }

    public static function rejectReward(int $rewardId, int $rejectedBy, ?string $notes = null): void
    {
        Database::update('referral_rewards', [
            'reward_status' => 'REJECTED',
            'approved_by' => $rejectedBy,
            'approved_at' => date('Y-m-d H:i:s'),
            'notes' => $notes,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'reward_id = ?', [$rewardId]);
    }

    /**
     * Summary stats for HR dashboard.
     */
    public static function summary(): array
    {
        return Database::fetch(
            "SELECT
                COUNT(DISTINCT r.referral_id) AS total_referrals,
                SUM(CASE WHEN rw.reward_status = 'PENDING' THEN 1 ELSE 0 END) AS pending_rewards,
                SUM(CASE WHEN rw.reward_status = 'APPROVED' THEN 1 ELSE 0 END) AS approved_rewards,
                SUM(CASE WHEN rw.reward_status = 'PAID' THEN 1 ELSE 0 END) AS paid_rewards,
                COALESCE(SUM(CASE WHEN rw.reward_status = 'PAID' THEN rw.reward_amount ELSE 0 END), 0) AS total_paid_amount
             FROM referrals r
             LEFT JOIN referral_rewards rw ON rw.referral_id = r.referral_id"
        ) ?: ['total_referrals' => 0, 'pending_rewards' => 0, 'approved_rewards' => 0, 'paid_rewards' => 0, 'total_paid_amount' => 0];
    }
}
