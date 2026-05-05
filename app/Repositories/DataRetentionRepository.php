<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\ApplicationStatus;
use App\Enums\RetentionAuditAction;

final class DataRetentionRepository
{
    public static function eligibleCandidates(int $retentionDays): array
    {
        return Database::fetchAll(
            "SELECT u.user_id, u.name, u.email, c.phone, c.resume_url, c.skill_keywords,
                    last_app.last_applied_at, a.status, j.status AS job_status, j.title AS job_title
             FROM users u
             JOIN candidates c ON c.candidate_id = u.user_id
             JOIN (
                SELECT candidate_id, MAX(applied_at) AS last_applied_at
                FROM applications
                GROUP BY candidate_id
             ) last_app ON last_app.candidate_id = c.candidate_id
             JOIN applications a ON a.candidate_id = c.candidate_id AND a.applied_at = last_app.last_applied_at
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE last_app.last_applied_at <= DATE_SUB(NOW(), INTERVAL ? DAY)
             AND NOT EXISTS (
                SELECT 1 FROM applications active_a
                JOIN job_requisitions active_j ON active_j.job_id = active_a.job_id
                WHERE active_a.candidate_id = c.candidate_id
                AND active_a.status != ?
                AND active_j.status != 'CLOSED'
             )
             ORDER BY last_app.last_applied_at ASC",
            [$retentionDays, ApplicationStatus::REJECTED->value]
        );
    }

    public static function eligibleCandidate(int $candidateId, int $retentionDays): ?array
    {
        foreach (self::eligibleCandidates($retentionDays) as $candidate) {
            if ((int) $candidate['user_id'] === $candidateId) {
                return $candidate;
            }
        }

        return null;
    }

    public static function anonymize(int $candidateId, int $actorId, int $retentionDays): bool
    {
        $candidate = self::eligibleCandidate($candidateId, $retentionDays);
        if (! $candidate) {
            return false;
        }

        Database::transaction(function () use ($candidate, $candidateId, $actorId): void {
            $email = 'anonymized-' . $candidateId . '-' . substr(hash('sha256', (string) $candidateId), 0, 12) . '@deleted.srim.local';
            Database::update('users', [
                'name' => 'Anonymized Candidate',
                'email' => $email,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'user_id = ?', [$candidateId]);

            Database::update('candidates', [
                'phone' => 'REDACTED',
                'resume_url' => null,
                'skill_keywords' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'candidate_id = ?', [$candidateId]);

            self::recordRetentionAudit($actorId, $candidateId, RetentionAuditAction::CANDIDATE_ANONYMIZED->value, $candidate, [
                'name' => ['old' => $candidate['name'], 'new' => 'Anonymized Candidate'],
                'email' => ['old' => $candidate['email'], 'new' => $email],
                'phone' => ['old' => $candidate['phone'], 'new' => 'REDACTED'],
                'resume_url' => ['old' => $candidate['resume_url'], 'new' => null],
                'skill_keywords' => ['old' => $candidate['skill_keywords'], 'new' => null],
            ]);
        });

        return true;
    }

    public static function delete(int $candidateId, int $actorId, int $retentionDays): bool
    {
        $candidate = self::eligibleCandidate($candidateId, $retentionDays);
        if (! $candidate) {
            return false;
        }

        Database::transaction(function () use ($candidate, $candidateId, $actorId): void {
            self::recordRetentionAudit($actorId, $candidateId, RetentionAuditAction::CANDIDATE_DELETED->value, $candidate, [
                'deleted_candidate' => $candidate,
            ]);

            $applications = Database::fetchAll('SELECT application_id FROM applications WHERE candidate_id = ?', [$candidateId]);
            $applicationIds = array_map(fn (array $row): int => (int) $row['application_id'], $applications);
            if ($applicationIds) {
                $appMarks = implode(',', array_fill(0, count($applicationIds), '?'));
                $interviews = Database::fetchAll("SELECT interview_id FROM interviews WHERE application_id IN ($appMarks)", $applicationIds);
                $interviewIds = array_map(fn (array $row): int => (int) $row['interview_id'], $interviews);
                if ($interviewIds) {
                    $intMarks = implode(',', array_fill(0, count($interviewIds), '?'));
                    Database::query("DELETE FROM interview_feedback WHERE interview_id IN ($intMarks)", $interviewIds);
                    Database::query("DELETE FROM interviewers_assignment WHERE interview_id IN ($intMarks)", $interviewIds);
                    Database::query("DELETE FROM interview_audit_records WHERE interview_id IN ($intMarks)", $interviewIds);
                    Database::query("DELETE FROM interviews WHERE interview_id IN ($intMarks)", $interviewIds);
                }

                $attempts = Database::fetchAll("SELECT ca_id FROM candidate_assessments WHERE application_id IN ($appMarks)", $applicationIds);
                $attemptIds = array_map(fn (array $row): int => (int) $row['ca_id'], $attempts);
                if ($attemptIds) {
                    $attemptMarks = implode(',', array_fill(0, count($attemptIds), '?'));
                    Database::query("DELETE FROM assessment_integrity_events WHERE ca_id IN ($attemptMarks)", $attemptIds);
                    Database::query("DELETE FROM submissions WHERE ca_id IN ($attemptMarks)", $attemptIds);
                    Database::query("DELETE FROM candidate_assessment_questions WHERE ca_id IN ($attemptMarks)", $attemptIds);
                    Database::query("DELETE FROM candidate_assessments WHERE ca_id IN ($attemptMarks)", $attemptIds);
                }

                $offers = Database::fetchAll("SELECT offer_id FROM offers WHERE application_id IN ($appMarks)", $applicationIds);
                $offerIds = array_map(fn (array $row): int => (int) $row['offer_id'], $offers);
                if ($offerIds) {
                    $offerMarks = implode(',', array_fill(0, count($offerIds), '?'));
                    Database::query("DELETE FROM onboarding WHERE offer_id IN ($offerMarks)", $offerIds);
                }
                Database::query("DELETE FROM post_offer_audit_records WHERE application_id IN ($appMarks)", $applicationIds);
                Database::query("DELETE FROM offers WHERE application_id IN ($appMarks)", $applicationIds);
                Database::query("DELETE FROM final_evaluations WHERE application_id IN ($appMarks)", $applicationIds);
                Database::query("DELETE FROM application_status_histories WHERE application_id IN ($appMarks)", $applicationIds);
                Database::query("DELETE FROM applications WHERE application_id IN ($appMarks)", $applicationIds);
            }

            Database::query('DELETE FROM notifications WHERE user_id = ?', [$candidateId]);
            Database::query('DELETE FROM candidates WHERE candidate_id = ?', [$candidateId]);
            Database::query('DELETE FROM users WHERE user_id = ?', [$candidateId]);
        });

        return true;
    }

    private static function recordRetentionAudit(int $actorId, int $targetId, string $action, array $snapshot, array $changes): void
    {
        Database::insert('account_audit_records', [
            'actor_user_id' => $actorId,
            'target_user_id' => $targetId,
            'action' => $action,
            'old_values' => json_encode(['snapshot' => $snapshot]),
            'new_values' => json_encode($changes),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
