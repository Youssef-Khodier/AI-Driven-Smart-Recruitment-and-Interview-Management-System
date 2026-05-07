<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Enums\ApplicationStatus;
use App\Enums\ArchiveActionStatus;
use App\Enums\ComplianceAuditAction;
use App\Enums\ComplianceRunCheckType;
use App\Enums\JobRequisitionStatus;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\NotificationModel;
use App\Models\OfferModel;

final class HrComplianceCheckController extends Controller
{
    public function index(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        return $this->view('hr/compliance/index', [
            'title' => 'Compliance Checks',
            'diversitySummary' => $this->diversitySummary(),
            'archiveSummary' => $this->archiveSummary(),
            'recentBatches' => $this->recentBatches(),
            'recentArchiveActions' => $this->recentArchiveActions(),
        ]);
    }

    public function diversity(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $summary = $this->diversitySummary();
        $this->recordComplianceAudit((int) $actor['user_id'], $actor['role'] ?? null, 'DIVERSITY_REPORT', null, ComplianceAuditAction::REPORT_GENERATED->value, null, [
            'total_consented_candidates' => $summary['total_consented_candidates'],
            'total_applications' => $summary['total_applications'],
        ], 'Diversity and inclusion aggregate report viewed.');

        return $this->view('hr/compliance/diversity', [
            'title' => 'Diversity & Inclusion Audit',
            'summary' => $summary,
        ]);
    }

    public function run(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $feedbackCreated = 0;
        $expiringCreated = 0;
        $expiredCreated = 0;
        $offersExpired = 0;
        $duplicateSkipped = 0;
        $batchId = $this->startBatch((int) $actor['user_id'], ComplianceRunCheckType::ALL_CHECKS->value);

        foreach (NotificationModel::findMissingFeedbackReminders() as $row) {
            $created = NotificationModel::createUnique(
                (int) $row['interviewer_id'],
                'Feedback Reminder',
                'Please submit feedback for ' . $row['candidate_name'] . ' from the interview scheduled on ' . date('Y-m-d H:i', strtotime($row['scheduled_at'])) . '.',
                NotificationType::FEEDBACK_REMINDER->value,
                (int) $row['interview_id'],
                'INTERVIEW'
            );
            if ($created !== null) {
                $feedbackCreated++;
            } else {
                $duplicateSkipped++;
            }
            $this->recordFinding($batchId, 'MISSING_FEEDBACK', 'HIGH', 'INTERVIEW', (int) $row['interview_id'], (int) $row['candidate_id'], (int) $row['interviewer_id'], $row['scheduled_at'], 'CREATE_NOTIFICATION', $created, $created === null ? $this->existingNotificationId((int) $row['interviewer_id'], NotificationType::FEEDBACK_REMINDER->value, (int) $row['interview_id'], 'INTERVIEW') : null, null, 'Feedback is overdue by at least 24 hours.');
        }

        foreach (NotificationModel::findOffersExpiringWithin48Hours() as $row) {
            $created = NotificationModel::createUnique(
                (int) $row['created_by'],
                'Offer Expiring Soon',
                'The offer for ' . $row['candidate_name'] . ' (' . $row['job_title'] . ') expires on ' . date('Y-m-d H:i', strtotime($row['expiry_date'])) . '.',
                NotificationType::OFFER_EXPIRING_SOON->value,
                (int) $row['offer_id'],
                'OFFER'
            );
            if ($created !== null) {
                $expiringCreated++;
            } else {
                $duplicateSkipped++;
            }
            $this->recordFinding($batchId, 'OFFER_EXPIRING', 'MEDIUM', 'OFFER', (int) $row['offer_id'], (int) $row['candidate_id'], (int) $row['created_by'], $row['expiry_date'], 'CREATE_NOTIFICATION', $created, $created === null ? $this->existingNotificationId((int) $row['created_by'], NotificationType::OFFER_EXPIRING_SOON->value, (int) $row['offer_id'], 'OFFER') : null, null, 'Sent offer expires within 48 hours.');
        }

        foreach (NotificationModel::findExpiredSentOffers() as $row) {
            if (OfferModel::enforceExpiryForOffer((int) $row['offer_id'], (int) $actor['user_id'])) {
                $offersExpired++;
            }
            $created = NotificationModel::createUnique(
                (int) $row['created_by'],
                'Offer Expired',
                'The offer for ' . $row['candidate_name'] . ' (' . $row['job_title'] . ') expired on ' . date('Y-m-d H:i', strtotime($row['expiry_date'])) . '.',
                NotificationType::OFFER_EXPIRED->value,
                (int) $row['offer_id'],
                'OFFER'
            );
            if ($created !== null) {
                $expiredCreated++;
            } else {
                $duplicateSkipped++;
            }
            $this->recordFinding($batchId, 'OFFER_EXPIRED', 'HIGH', 'OFFER', (int) $row['offer_id'], (int) $row['candidate_id'], (int) $row['created_by'], $row['expiry_date'], 'EXPIRE_OFFER_AND_NOTIFY', $created, $created === null ? $this->existingNotificationId((int) $row['created_by'], NotificationType::OFFER_EXPIRED->value, (int) $row['offer_id'], 'OFFER') : null, null, 'Sent offer passed its expiry date.');
        }

        $newNotifications = $feedbackCreated + $expiringCreated + $expiredCreated;
        $summary = "Checks complete. Feedback reminders: $feedbackCreated. Expiring offer alerts: $expiringCreated. Expired offer alerts: $expiredCreated. Offers expired: $offersExpired.";
        $this->completeBatch($batchId, $newNotifications, $duplicateSkipped, 0, $summary);
        $this->recordComplianceAudit((int) $actor['user_id'], $actor['role'] ?? null, 'COMPLIANCE_RUN', $batchId, ComplianceAuditAction::RUN_CHECK_EXECUTED->value, null, ['new_notifications' => $newNotifications, 'offers_expired' => $offersExpired], $summary);

        Session::flash('status', $summary);

        return $this->redirect(url('hr.compliance.index'));
    }

    public function archive(Request $request): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $now = date('Y-m-d H:i:s');
        $closedArchived = 0;
        $rejectedArchived = 0;
        $batchId = $this->startBatch((int) $actor['user_id'], ComplianceRunCheckType::ALL_CHECKS->value, ['archive' => true]);

        $closedRequisitions = Database::fetchAll(
            'SELECT job_id, title, status, closed_at FROM job_requisitions WHERE status = ? AND archived_at IS NULL ORDER BY closed_at ASC, updated_at ASC',
            [JobRequisitionStatus::CLOSED->value]
        );
        foreach ($closedRequisitions as $row) {
            Database::update('job_requisitions', ['archived_at' => $now, 'archived_by' => $actor['user_id'], 'updated_at' => $now], 'job_id = ?', [(int) $row['job_id']]);
            $this->recordArchiveAction('JOB_REQUISITION', (int) $row['job_id'], (int) $actor['user_id'], $row['status'], 'ARCHIVED', 'Closed requisition archived by integrity manager.', $row);
            $this->recordFinding($batchId, 'ARCHIVE_CLOSED_REQUISITION', 'LOW', 'JOB_REQUISITION', (int) $row['job_id'], null, (int) $actor['user_id'], $row['closed_at'], 'ARCHIVE', null, null, ArchiveActionStatus::ARCHIVED->value, 'Closed requisition archived.');
            $closedArchived++;
        }

        $rejectedApplications = Database::fetchAll(
            'SELECT a.application_id, a.candidate_id, a.status, a.updated_at, u.name AS candidate_name, j.title AS job_title
             FROM applications a
             JOIN users u ON u.user_id = a.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE a.status = ? AND a.archived_at IS NULL
             ORDER BY a.updated_at ASC',
            [ApplicationStatus::REJECTED->value]
        );
        foreach ($rejectedApplications as $row) {
            Database::update('applications', ['archived_at' => $now, 'archived_by' => $actor['user_id'], 'updated_at' => $now], 'application_id = ?', [(int) $row['application_id']]);
            $this->recordArchiveAction('APPLICATION', (int) $row['application_id'], (int) $actor['user_id'], $row['status'], 'ARCHIVED', 'Rejected candidate application archived by integrity manager.', $row);
            $this->recordFinding($batchId, 'ARCHIVE_REJECTED_CANDIDATE', 'LOW', 'APPLICATION', (int) $row['application_id'], (int) $row['candidate_id'], (int) $actor['user_id'], $row['updated_at'], 'ARCHIVE', null, null, ArchiveActionStatus::ARCHIVED->value, 'Rejected candidate application archived.');
            $rejectedArchived++;
        }

        $summary = "Archive complete. Closed requisitions archived: $closedArchived. Rejected candidate applications archived: $rejectedArchived.";
        $this->completeBatch($batchId, 0, 0, $closedArchived + $rejectedArchived, $summary);
        $this->recordComplianceAudit((int) $actor['user_id'], $actor['role'] ?? null, 'ARCHIVE_BATCH', $batchId, ComplianceAuditAction::ARCHIVE_APPROVED->value, null, ['closed_requisitions' => $closedArchived, 'rejected_applications' => $rejectedArchived], $summary);
        Session::flash('status', $summary);

        return $this->redirect(url('hr.compliance.index'));
    }

    private function diversitySummary(): array
    {
        $totalCandidates = Database::fetch(
            'SELECT COUNT(*) AS total FROM candidate_demographics WHERE consent_flag = 1 AND withdrawn_at IS NULL'
        );
        $totalApplications = Database::fetch(
            'SELECT COUNT(*) AS total
             FROM applications a
             JOIN candidate_demographics cd ON cd.candidate_id = a.candidate_id
             WHERE cd.consent_flag = 1 AND cd.withdrawn_at IS NULL'
        );

        return [
            'total_consented_candidates' => (int) ($totalCandidates['total'] ?? 0),
            'total_applications' => (int) ($totalApplications['total'] ?? 0),
            'gender' => $this->demographicCounts('gender_category'),
            'ethnicity' => $this->demographicCounts('ethnicity_category'),
            'disability' => $this->demographicCounts('disability_category'),
            'veteran' => $this->demographicCounts('veteran_status_category'),
            'pipeline' => Database::fetchAll(
                "SELECT a.status, COALESCE(cd.gender_category, 'Unspecified') AS category, COUNT(*) AS count
                 FROM applications a
                 JOIN candidate_demographics cd ON cd.candidate_id = a.candidate_id
                 WHERE cd.consent_flag = 1 AND cd.withdrawn_at IS NULL
                 GROUP BY a.status, COALESCE(cd.gender_category, 'Unspecified')
                 ORDER BY a.status, category"
            ),
        ];
    }

    private function demographicCounts(string $column): array
    {
        return Database::fetchAll(
            "SELECT COALESCE($column, 'Unspecified') AS category, COUNT(*) AS count
             FROM candidate_demographics
             WHERE consent_flag = 1 AND withdrawn_at IS NULL
             GROUP BY COALESCE($column, 'Unspecified')
             ORDER BY count DESC, category"
        );
    }

    private function archiveSummary(): array
    {
        $closed = Database::fetch('SELECT COUNT(*) AS count FROM job_requisitions WHERE status = ? AND archived_at IS NULL', [JobRequisitionStatus::CLOSED->value]);
        $rejected = Database::fetch('SELECT COUNT(*) AS count FROM applications WHERE status = ? AND archived_at IS NULL', [ApplicationStatus::REJECTED->value]);

        return [
            'closed_requisitions' => (int) ($closed['count'] ?? 0),
            'rejected_applications' => (int) ($rejected['count'] ?? 0),
        ];
    }

    private function recentBatches(): array
    {
        return Database::fetchAll(
            'SELECT b.*, u.name AS actor_name FROM compliance_run_check_batches b JOIN users u ON u.user_id = b.actor_user_id ORDER BY b.started_at DESC LIMIT 8'
        );
    }

    private function recentArchiveActions(): array
    {
        return Database::fetchAll(
            'SELECT aa.*, u.name AS actor_name FROM archive_actions aa JOIN users u ON u.user_id = aa.actor_user_id ORDER BY aa.action_timestamp DESC LIMIT 8'
        );
    }

    private function startBatch(int $actorId, string $checkType, ?array $scope = null): int
    {
        return Database::insert('compliance_run_check_batches', [
            'actor_user_id' => $actorId,
            'check_type' => $checkType,
            'selected_scope' => $scope ? json_encode($scope) : null,
            'status' => 'STARTED',
            'started_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function completeBatch(int $batchId, int $newNotifications, int $duplicateSkipped, int $archiveRecommendations, string $summary): void
    {
        $count = Database::fetch('SELECT COUNT(*) AS count FROM compliance_run_check_findings WHERE batch_id = ?', [$batchId]);
        Database::update('compliance_run_check_batches', [
            'status' => 'COMPLETED',
            'total_findings' => (int) ($count['count'] ?? 0),
            'new_notifications' => $newNotifications,
            'duplicate_notifications_skipped' => $duplicateSkipped,
            'archive_recommendations' => $archiveRecommendations,
            'summary_message' => $summary,
            'completed_at' => date('Y-m-d H:i:s'),
        ], 'batch_id = ?', [$batchId]);
    }

    private function recordFinding(int $batchId, string $type, string $severity, string $entityType, int $entityId, ?int $candidateId, ?int $responsibleUserId, ?string $dueDate, string $recommendedAction, ?int $createdNotificationId, ?int $existingNotificationId, ?string $archiveStatus, string $reason): void
    {
        Database::insert('compliance_run_check_findings', [
            'batch_id' => $batchId,
            'finding_type' => $type,
            'severity' => $severity,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'candidate_id' => $candidateId,
            'responsible_user_id' => $responsibleUserId,
            'due_date' => $dueDate,
            'recommended_action' => $recommendedAction,
            'existing_notification_id' => $existingNotificationId,
            'created_notification_id' => $createdNotificationId,
            'archive_eligibility_status' => $archiveStatus,
            'reason' => $reason,
            'detected_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function existingNotificationId(int $userId, string $type, int $referenceId, string $referenceType): ?int
    {
        $row = Database::fetch(
            'SELECT notification_id FROM notifications WHERE user_id = ? AND type = ? AND reference_id = ? AND reference_type = ? ORDER BY notification_id DESC LIMIT 1',
            [$userId, $type, $referenceId, $referenceType]
        );

        return $row ? (int) $row['notification_id'] : null;
    }

    private function recordArchiveAction(string $entityType, int $entityId, int $actorId, ?string $previousStatus, string $newStatus, string $reason, array $snapshot): void
    {
        Database::insert('archive_actions', [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action_status' => ArchiveActionStatus::ARCHIVED->value,
            'reason' => $reason,
            'eligibility_snapshot' => json_encode($snapshot),
            'actor_user_id' => $actorId,
            'previous_active_status' => $previousStatus,
            'new_archive_status' => $newStatus,
            'affected_record_summary' => json_encode(['entity_type' => $entityType, 'entity_id' => $entityId]),
            'action_timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private function recordComplianceAudit(int $actorId, ?string $actorRole, string $entityType, ?int $entityId, string $action, ?array $oldValues, ?array $newValues, ?string $reason): void
    {
        Database::insert('compliance_audit_events', [
            'actor_user_id' => $actorId,
            'actor_role' => $actorRole,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues !== null ? json_encode($oldValues) : null,
            'new_values' => $newValues !== null ? json_encode($newValues) : null,
            'reason' => $reason,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
