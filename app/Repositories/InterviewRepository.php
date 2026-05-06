<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\InterviewStatus;
use App\Enums\UserRole;

final class InterviewRepository
{
    public static function findEligibleApplicationForScheduling(int $applicationId): ?array
    {
        return Database::fetch(
            "SELECT a.*, u.name AS candidate_name, u.email AS candidate_email, c.current_title, c.years_experience, c.location, c.resume_url, j.title AS job_title, j.department_id, d.name AS department_name
             FROM applications a
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             JOIN departments d ON d.department_id = j.department_id
             WHERE a.application_id = ? AND a.status = ?",
            [$applicationId, ApplicationStatus::INTERVIEW->value]
        );
    }

    public static function findForCandidate(int $interviewId, int $candidateId): ?array
    {
        return Database::fetch(
            "SELECT i.*, a.candidate_id, j.title AS job_title 
             FROM interviews i
             JOIN applications a ON a.application_id = i.application_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE i.interview_id = ? AND a.candidate_id = ?",
            [$interviewId, $candidateId]
        );
    }

    public static function effectiveDurationMinutes(array $interview): int
    {
        return (int)($interview['duration_minutes'] ?? 0) + (int)($interview['extended_duration_minutes'] ?? 0);
    }

    public static function checkCandidateOwnership(int $applicationId, int $candidateId): bool
    {
        $app = Database::fetch("SELECT application_id FROM applications WHERE application_id = ? AND candidate_id = ?", [$applicationId, $candidateId]);
        return $app !== false;
    }

    public static function activePanelUsers(): array
    {
        return Database::fetchAll(
            "SELECT * FROM users
             WHERE status = ? AND role IN (?, ?, ?)
             ORDER BY FIELD(role, ?, ?, ?), name",
            [
                AccountStatus::ACTIVE->value,
                UserRole::INTERVIEWER->value, UserRole::HR_ADMIN->value, UserRole::JUNIOR_STAFF->value,
                UserRole::INTERVIEWER->value, UserRole::HR_ADMIN->value, UserRole::JUNIOR_STAFF->value
            ]
        );
    }

    public static function hasScheduleConflict(?int $ignoreInterviewId, int $applicationId, array $panelUserIds, string $scheduledAt, int $durationMinutes): bool
    {
        if (empty($panelUserIds)) {
            return false;
        }

        $start = date('Y-m-d H:i:s', strtotime($scheduledAt));
        $end = date('Y-m-d H:i:s', strtotime($scheduledAt) + $durationMinutes * 60);

        $params = [$start, $end, $start, $end, $start, $end, $applicationId, ...$panelUserIds];
        
        $sql = "SELECT COUNT(*) as count FROM interviews i
                LEFT JOIN interviewers_assignment ia ON ia.interview_id = i.interview_id
                WHERE i.status != '" . InterviewStatus::CANCELLED->value . "'
                AND (
                    (? >= i.scheduled_at AND ? < DATE_ADD(i.scheduled_at, INTERVAL i.duration_minutes MINUTE)) OR
                    (? > i.scheduled_at AND ? <= DATE_ADD(i.scheduled_at, INTERVAL i.duration_minutes MINUTE)) OR
                    (? <= i.scheduled_at AND ? >= DATE_ADD(i.scheduled_at, INTERVAL i.duration_minutes MINUTE))
                )
                AND (i.application_id = ? OR ia.interviewer_id IN (" . implode(',', array_fill(0, count($panelUserIds), '?')) . "))";

        if ($ignoreInterviewId !== null) {
            $sql .= " AND i.interview_id != ?";
            $params[] = $ignoreInterviewId;
        }

        $result = Database::fetch($sql, $params);
        return (int) ($result['count'] ?? 0) > 0;
    }

    public static function createInterviewWithAssignments(array $interviewData, array $assignments, int $actorUserId): int
    {
        return Database::transaction(function () use ($interviewData, $assignments, $actorUserId) {
            $interviewId = Database::insert('interviews', [
                'application_id' => $interviewData['application_id'],
                'interview_type' => $interviewData['interview_type'],
                'scheduled_at' => $interviewData['scheduled_at'],
                'duration_minutes' => $interviewData['duration_minutes'],
                'status' => InterviewStatus::SCHEDULED->value,
                'created_by' => $actorUserId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            foreach ($assignments as $assignment) {
                Database::insert('interviewers_assignment', [
                    'interview_id' => $interviewId,
                    'interviewer_id' => $assignment['interviewer_id'],
                    'role_in_panel' => $assignment['role_in_panel'],
                    'is_shadowing' => $assignment['is_shadowing'] ? 1 : 0,
                ]);
            }

            InterviewAuditRepository::record($interviewId, $actorUserId, \App\Enums\InterviewAuditAction::SCHEDULED->value, [
                'interview_type' => $interviewData['interview_type'],
                'scheduled_at' => $interviewData['scheduled_at'],
                'duration_minutes' => $interviewData['duration_minutes'],
                'assignments' => $assignments,
            ]);

            return $interviewId;
        });
    }

    public static function hrInterviewList(): array
    {
        $interviews = Database::fetchAll(
            "SELECT i.*, a.candidate_id, u.name AS candidate_name, j.title AS job_title
             FROM interviews i
             JOIN applications a ON a.application_id = i.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             ORDER BY i.scheduled_at DESC"
        );
        
        $assignments = Database::fetchAll(
            "SELECT ia.*, u.name AS interviewer_name 
             FROM interviewers_assignment ia 
             JOIN users u ON u.user_id = ia.interviewer_id"
        );
        
        $groupedAssignments = [];
        foreach ($assignments as $a) {
            $groupedAssignments[$a['interview_id']][] = $a;
        }
        
        foreach ($interviews as &$interview) {
            $interview['assignments'] = $groupedAssignments[$interview['interview_id']] ?? [];
        }
        
        return $interviews;
    }

    public static function findForHr(int $interviewId): ?array
    {
        $interview = Database::fetch(
            "SELECT i.*, a.candidate_id, a.status AS application_status, a.match_score, u.name AS candidate_name, u.email AS candidate_email, c.current_title, c.years_experience, j.title AS job_title
             FROM interviews i
             JOIN applications a ON a.application_id = i.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE i.interview_id = ?",
            [$interviewId]
        );

        if (!$interview) {
            return null;
        }

        $interview['assignments'] = Database::fetchAll(
            "SELECT ia.*, u.name AS interviewer_name, u.role AS interviewer_role
             FROM interviewers_assignment ia
             JOIN users u ON u.user_id = ia.interviewer_id
             WHERE ia.interview_id = ?
             ORDER BY ia.role_in_panel, u.name",
            [$interviewId]
        );

        $interview['audit_records'] = Database::fetchAll(
            "SELECT ar.*, u.name AS actor_name
             FROM interview_audit_records ar
             JOIN users u ON u.user_id = ar.actor_user_id
             WHERE ar.interview_id = ?
             ORDER BY ar.created_at DESC",
            [$interviewId]
        );

        return $interview;
    }

    public static function assignedInterviewList(int $userId): array
    {
        $interviews = Database::fetchAll(
            "SELECT i.*, a.candidate_id, u.name AS candidate_name, j.title AS job_title, ia.role_in_panel
             FROM interviews i
             JOIN interviewers_assignment ia ON ia.interview_id = i.interview_id
             JOIN applications a ON a.application_id = i.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE ia.interviewer_id = ?
             ORDER BY i.scheduled_at DESC",
            [$userId]
        );
        return $interviews;
    }

    public static function findAssignment(int $interviewId, int $userId): ?array
    {
        return Database::fetch(
            "SELECT * FROM interviewers_assignment WHERE interview_id = ? AND interviewer_id = ?",
            [$interviewId, $userId]
        );
    }

    public static function briefingForAssignedUser(int $interviewId, int $userId): ?array
    {
        $assignment = self::findAssignment($interviewId, $userId);
        if (!$assignment) {
            return null;
        }

        $briefing = Database::fetch(
            "SELECT i.*, a.candidate_id, a.status AS application_status, a.match_score, a.applied_at, u.name AS candidate_name, u.email AS candidate_email, c.current_title, c.years_experience, c.location, c.resume_url, j.title AS job_title, j.requirements
             FROM interviews i
             JOIN applications a ON a.application_id = i.application_id
             JOIN candidates c ON c.candidate_id = a.candidate_id
             JOIN users u ON u.user_id = c.candidate_id
             JOIN job_requisitions j ON j.job_id = a.job_id
             WHERE i.interview_id = ?",
            [$interviewId]
        );

        if (!$briefing) {
            return null;
        }
        $briefing['assignment'] = $assignment;

        // Load latest assessment attempt for candidate and job
        $briefing['assessment_attempt'] = Database::fetch(
            "SELECT ca.*, asmt.title AS assessment_title
             FROM candidate_assessments ca
             JOIN assessments asmt ON asmt.assessment_id = ca.assessment_id
             WHERE ca.application_id = ?
             ORDER BY ca.created_at DESC LIMIT 1",
            [$briefing['application_id']]
        );

        if ($briefing['assessment_attempt']) {
            $briefing['submissions_summary'] = Database::fetchAll(
                "SELECT s.*, q.question_text, q.type
                 FROM submissions s
                 JOIN candidate_assessment_questions caq ON caq.attempt_question_id = s.attempt_question_id
                 LEFT JOIN questions q ON q.question_id = s.question_id
                 WHERE s.ca_id = ?",
                [$briefing['assessment_attempt']['ca_id']]
            );
        } else {
            $briefing['submissions_summary'] = [];
        }

        return $briefing;
    }

    public static function markCompleted(int $interviewId, int $actorUserId): void
    {
        Database::transaction(function () use ($interviewId, $actorUserId) {
            Database::update('interviews', [
                'status' => InterviewStatus::COMPLETED->value,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ?', [$interviewId]);

            InterviewAuditRepository::record($interviewId, $actorUserId, \App\Enums\InterviewAuditAction::COMPLETED->value, [
                'status' => InterviewStatus::COMPLETED->value,
            ]);
        });
    }
}
