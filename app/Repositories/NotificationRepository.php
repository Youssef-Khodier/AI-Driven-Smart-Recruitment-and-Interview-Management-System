<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\NotificationType;
use App\Enums\OfferStatus;

final class NotificationRepository
{
    public static function createUnique(int $userId, string $title, string $message, string $type, ?int $referenceId = null, ?string $referenceType = null): ?int
    {
        if (self::exists($userId, $type, $referenceId, $referenceType)) {
            return null;
        }

        return Database::insert('notifications', [
            'user_id' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'reference_id' => $referenceId,
            'reference_type' => $referenceType,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function exists(int $userId, string $type, ?int $referenceId, ?string $referenceType): bool
    {
        $row = Database::fetch(
            'SELECT notification_id FROM notifications
             WHERE user_id = ? AND type = ?
             AND ((reference_id = ?) OR (reference_id IS NULL AND ? IS NULL))
             AND ((reference_type = ?) OR (reference_type IS NULL AND ? IS NULL))
             LIMIT 1',
            [$userId, $type, $referenceId, $referenceId, $referenceType, $referenceType]
        );

        return $row !== null;
    }

    public static function unreadCount(int $userId): int
    {
        $row = Database::fetch('SELECT COUNT(*) AS count FROM notifications WHERE user_id = ? AND is_read = 0', [$userId]);

        return (int) ($row['count'] ?? 0);
    }

    public static function listForUser(int $userId, int $page = 1, int $perPage = 15): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        return Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC, notification_id DESC LIMIT $perPage OFFSET $offset",
            [$userId]
        );
    }

    public static function countForUser(int $userId): int
    {
        $row = Database::fetch('SELECT COUNT(*) AS count FROM notifications WHERE user_id = ?', [$userId]);

        return (int) ($row['count'] ?? 0);
    }

    public static function find(int $notificationId): ?array
    {
        return Database::fetch('SELECT * FROM notifications WHERE notification_id = ?', [$notificationId]);
    }

    public static function markRead(int $notificationId, int $userId): void
    {
        Database::update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ], 'notification_id = ? AND user_id = ? AND is_read = 0', [$notificationId, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        Database::update('notifications', [
            'is_read' => 1,
            'read_at' => date('Y-m-d H:i:s'),
        ], 'user_id = ? AND is_read = 0', [$userId]);
    }

    public static function createApplicationStatusNotification(int $applicationId, string $newStatus): ?int
    {
        $application = Database::fetch(
            'SELECT a.application_id, a.candidate_id, j.title AS job_title
             FROM applications a
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        if (! $application) {
            return null;
        }

        return self::createUnique(
            (int) $application['candidate_id'],
            'Application Status Updated',
            'Your application for ' . $application['job_title'] . ' is now ' . $newStatus . '.',
            NotificationType::STATUS_CHANGE->value,
            $applicationId,
            'APPLICATION'
        );
    }

    public static function findMissingFeedbackReminders(): array
    {
        return Database::fetchAll(
            "SELECT i.interview_id, i.scheduled_at, i.updated_at, ia.interviewer_id,
                    candidate_user.name AS candidate_name, j.title AS job_title
             FROM interviews i
             JOIN interviewers_assignment ia ON ia.interview_id = i.interview_id
             JOIN applications a ON a.application_id = i.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users candidate_user ON candidate_user.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             LEFT JOIN interview_feedback f ON f.interview_id = i.interview_id AND f.interviewer_id = ia.interviewer_id
             WHERE i.status = 'COMPLETED'
             AND COALESCE(i.updated_at, i.scheduled_at) <= DATE_SUB(NOW(), INTERVAL 24 HOUR)
             AND f.feedback_id IS NULL
             ORDER BY i.scheduled_at ASC"
        );
    }

    public static function findOffersExpiringWithin48Hours(): array
    {
        return Database::fetchAll(
            'SELECT o.offer_id, o.expiry_date, o.created_by, u.name AS candidate_name, j.title AS job_title
             FROM offers o
             JOIN applications a ON a.application_id = o.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE o.status = ? AND o.expiry_date > NOW() AND o.expiry_date <= DATE_ADD(NOW(), INTERVAL 48 HOUR)
             ORDER BY o.expiry_date ASC',
            [OfferStatus::SENT->value]
        );
    }

    public static function findExpiredSentOffers(): array
    {
        return Database::fetchAll(
            'SELECT o.offer_id, o.expiry_date, o.created_by, u.name AS candidate_name, j.title AS job_title
             FROM offers o
             JOIN applications a ON a.application_id = o.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE o.status = ? AND o.expiry_date <= NOW()
             ORDER BY o.expiry_date ASC',
            [OfferStatus::SENT->value]
        );
    }
}
