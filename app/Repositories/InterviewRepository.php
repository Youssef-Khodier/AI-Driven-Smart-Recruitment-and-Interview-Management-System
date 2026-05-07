<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\AccountStatus;
use App\Enums\ApplicationStatus;
use App\Enums\InterviewAssignmentRole;
use App\Enums\InterviewAuditAction;
use App\Enums\InterviewExtensionStatus;
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

    public static function recommendedPanel(int $applicationId, string $scheduledAt, int $durationMinutes, int $actorUserId): array
    {
        $users = Database::fetchAll(
            "SELECT u.user_id, u.name, u.email, u.role,
                    COALESCE(spc.can_represent_hr, u.role = ?) AS can_represent_hr,
                    COALESCE(spc.can_lead_technical, u.role = ?) AS can_lead_technical,
                    COALESCE(spc.can_interview, u.role IN (?, ?)) AS can_interview,
                    COALESCE(spc.can_observe, u.role IN (?, ?, ?)) AS can_observe,
                    COUNT(CASE WHEN i.status != ? THEN ia.assignment_id END) AS active_assignment_count
             FROM users u
             LEFT JOIN staff_panel_capabilities spc ON spc.user_id = u.user_id
             LEFT JOIN interviewers_assignment ia ON ia.interviewer_id = u.user_id
             LEFT JOIN interviews i ON i.interview_id = ia.interview_id
             WHERE u.status = ? AND u.role IN (?, ?, ?)
             GROUP BY u.user_id, u.name, u.email, u.role, spc.can_represent_hr, spc.can_lead_technical, spc.can_interview, spc.can_observe
             ORDER BY active_assignment_count ASC, FIELD(u.role, ?, ?, ?), u.name ASC",
            [
                UserRole::HR_ADMIN->value,
                UserRole::INTERVIEWER->value,
                UserRole::INTERVIEWER->value,
                UserRole::JUNIOR_STAFF->value,
                UserRole::HR_ADMIN->value,
                UserRole::INTERVIEWER->value,
                UserRole::JUNIOR_STAFF->value,
                InterviewStatus::CANCELLED->value,
                AccountStatus::ACTIVE->value,
                UserRole::HR_ADMIN->value,
                UserRole::INTERVIEWER->value,
                UserRole::JUNIOR_STAFF->value,
                UserRole::HR_ADMIN->value,
                UserRole::INTERVIEWER->value,
                UserRole::JUNIOR_STAFF->value,
            ]
        );

        $picked = [];
        $used = [];
        $roles = [
            InterviewAssignmentRole::HR_REPRESENTATIVE->value => 'can_represent_hr',
            InterviewAssignmentRole::INTERVIEWER->value => 'can_lead_technical',
            InterviewAssignmentRole::OBSERVER->value => 'can_observe',
        ];

        foreach ($roles as $panelRole => $capability) {
            foreach ($users as $user) {
                if (isset($used[(int)$user['user_id']]) || empty($user[$capability])) {
                    continue;
                }

                $picked[] = [
                    'user_id' => (int)$user['user_id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'user_role' => $user['role'],
                    'role_in_panel' => $panelRole,
                    'active_assignment_count' => (int)$user['active_assignment_count'],
                ];
                $used[(int)$user['user_id']] = true;
                break;
            }
        }

        $payload = [
            'recommended_panel' => $picked,
            'available_staff' => $users,
        ];

        Database::insert('panel_recommendation_snapshots', [
            'application_id' => $applicationId,
            'requested_start_at' => date('Y-m-d H:i:s', strtotime($scheduledAt)),
            'requested_duration_minutes' => $durationMinutes,
            'required_panel_mix' => json_encode(['HR_REPRESENTATIVE', 'INTERVIEWER', 'OBSERVER_OPTIONAL']),
            'recommendation_payload' => json_encode($payload),
            'generated_by' => $actorUserId,
            'generated_at' => date('Y-m-d H:i:s'),
        ]);

        return $payload;
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
                    (? >= i.scheduled_at AND ? < DATE_ADD(i.scheduled_at, INTERVAL (i.duration_minutes + i.extended_duration_minutes) MINUTE)) OR
                    (? > i.scheduled_at AND ? <= DATE_ADD(i.scheduled_at, INTERVAL (i.duration_minutes + i.extended_duration_minutes) MINUTE)) OR
                    (? <= i.scheduled_at AND ? >= DATE_ADD(i.scheduled_at, INTERVAL (i.duration_minutes + i.extended_duration_minutes) MINUTE))
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
                    'assignment_source' => $assignment['assignment_source'] ?? 'MANUAL',
                    'assigned_by' => $actorUserId,
                    'assigned_at' => date('Y-m-d H:i:s'),
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

    public static function briefingSnapshot(int $interviewId): ?array
    {
        return Database::fetch(
            "SELECT * FROM interview_briefing_snapshots WHERE interview_id = ?",
            [$interviewId]
        );
    }

    public static function refreshBriefingSnapshot(int $interviewId, int $actorUserId): array
    {
        $briefing = self::briefingForInterview($interviewId);
        if (!$briefing) {
            throw new \RuntimeException('Interview briefing data not found.');
        }

        $missing = [];
        if (empty($briefing['resume_url'])) {
            $missing[] = 'resume';
        }
        if (empty($briefing['assessment_attempt'])) {
            $missing[] = 'assessment';
        }
        if (empty($briefing['requirements'])) {
            $missing[] = 'job_requirements';
        }

        $candidateSummary = trim(sprintf(
            "%s | %s | %s years experience | %s | application %s | match %s%%",
            $briefing['candidate_name'],
            $briefing['current_title'] ?: 'Current title not provided',
            (string)($briefing['years_experience'] ?? '0'),
            $briefing['location'] ?: 'Location not provided',
            $briefing['application_status'],
            (string)($briefing['match_score'] ?? '0')
        ));

        $assessment = $briefing['assessment_attempt'];
        $assessmentSummary = $assessment
            ? sprintf(
                "%s | status %s | score %s | %d submitted answer(s)",
                $assessment['assessment_title'],
                $assessment['status'],
                (string)($assessment['score'] ?? 'Pending'),
                count($briefing['submissions_summary'])
            )
            : 'No assessment attempt found for this application.';

        $data = [
            'candidate_summary' => $candidateSummary,
            'assessment_summary' => $assessmentSummary,
            'job_requirements_summary' => str_limit_local($briefing['requirements'] ?? '', 900),
            'missing_data_flags' => json_encode($missing),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        $existing = self::briefingSnapshot($interviewId);
        if ($existing) {
            Database::update('interview_briefing_snapshots', $data, 'interview_id = ?', [$interviewId]);
        } else {
            Database::insert('interview_briefing_snapshots', [
                'interview_id' => $interviewId,
                ...$data,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        }

        InterviewAuditRepository::record($interviewId, $actorUserId, InterviewAuditAction::BRIEFING_CREATED->value, $data);

        return self::briefingSnapshot($interviewId) ?? [];
    }

    public static function briefingForInterview(int $interviewId): ?array
    {
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

        $briefing['assessment_attempt'] = Database::fetch(
            "SELECT ca.*, asmt.title AS assessment_title
             FROM candidate_assessments ca
             JOIN assessments asmt ON asmt.assessment_id = ca.assessment_id
             WHERE ca.application_id = ?
             ORDER BY ca.created_at DESC LIMIT 1",
            [$briefing['application_id']]
        );

        $briefing['submissions_summary'] = [];
        if ($briefing['assessment_attempt']) {
            $briefing['submissions_summary'] = Database::fetchAll(
                "SELECT s.*, q.question_text, q.type
                 FROM submissions s
                 JOIN candidate_assessment_questions caq ON caq.attempt_question_id = s.attempt_question_id
                 LEFT JOIN questions q ON q.question_id = s.question_id
                 WHERE s.ca_id = ?",
                [$briefing['assessment_attempt']['ca_id']]
            );
        }

        return $briefing;
    }

    public static function workspaceForInterview(int $interviewId): array
    {
        $workspace = Database::fetch(
            "SELECT w.*, u.name AS last_saved_by_name
             FROM simulated_coding_workspaces w
             JOIN users u ON u.user_id = w.last_saved_by
             WHERE w.interview_id = ?",
            [$interviewId]
        );

        return $workspace ?: [
            'workspace_id' => null,
            'interview_id' => $interviewId,
            'prompt_text' => '',
            'code_text' => '',
            'candidate_run_notes' => '',
            'interviewer_notes' => '',
            'version_number' => 0,
            'last_saved_by_name' => null,
            'last_saved_at' => null,
        ];
    }

    public static function workspaceHistory(int $interviewId): array
    {
        return Database::fetchAll(
            "SELECT wh.*, u.name AS actor_name
             FROM workspace_history_records wh
             JOIN users u ON u.user_id = wh.actor_user_id
             WHERE wh.interview_id = ?
             ORDER BY wh.created_at DESC",
            [$interviewId]
        );
    }

    public static function saveWorkspaceSnapshot(int $interviewId, array $data, int $actorUserId, string $changedSection): int
    {
        return Database::transaction(function () use ($interviewId, $data, $actorUserId, $changedSection) {
            $existing = self::workspaceForInterview($interviewId);
            $previousVersion = (int)($existing['version_number'] ?? 0);
            $newVersion = $previousVersion + 1;

            $payload = [
                'prompt_text' => $data['prompt_text'] ?? '',
                'code_text' => $data['code_text'] ?? '',
                'candidate_run_notes' => $data['candidate_run_notes'] ?? '',
                'interviewer_notes' => $data['interviewer_notes'] ?? '',
                'version_number' => $newVersion,
                'last_saved_by' => $actorUserId,
                'last_saved_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            if (!empty($existing['workspace_id'])) {
                Database::update('simulated_coding_workspaces', $payload, 'workspace_id = ?', [(int)$existing['workspace_id']]);
                $workspaceId = (int)$existing['workspace_id'];
            } else {
                $workspaceId = Database::insert('simulated_coding_workspaces', [
                    'interview_id' => $interviewId,
                    ...$payload,
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }

            Database::insert('workspace_history_records', [
                'workspace_id' => $workspaceId,
                'interview_id' => $interviewId,
                'actor_user_id' => $actorUserId,
                'changed_section' => $changedSection,
                'previous_version_number' => $previousVersion,
                'new_version_number' => $newVersion,
                'change_summary' => 'Saved workspace snapshot.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            InterviewAuditRepository::record($interviewId, $actorUserId, InterviewAuditAction::WORKSPACE_UPDATED->value, [
                'version_number' => $newVersion,
                'changed_section' => $changedSection,
            ]);

            return $workspaceId;
        });
    }

    public static function extensionRequests(int $interviewId): array
    {
        return Database::fetchAll(
            "SELECT er.*, requester.name AS requested_by_name, decider.name AS decided_by_name
             FROM interview_extension_requests er
             JOIN users requester ON requester.user_id = er.requested_by
             LEFT JOIN users decider ON decider.user_id = er.decided_by
             WHERE er.interview_id = ?
             ORDER BY er.requested_at DESC",
            [$interviewId]
        );
    }

    public static function findExtensionRequest(int $interviewId, int $requestId): ?array
    {
        return Database::fetch(
            "SELECT er.*, requester.name AS requested_by_name, decider.name AS decided_by_name
             FROM interview_extension_requests er
             JOIN users requester ON requester.user_id = er.requested_by
             LEFT JOIN users decider ON decider.user_id = er.decided_by
             WHERE er.interview_id = ? AND er.extension_request_id = ?",
            [$interviewId, $requestId]
        );
    }

    public static function requestExtension(int $interviewId, int $requestedBy, int $minutes, string $reason): int
    {
        return Database::transaction(function () use ($interviewId, $requestedBy, $minutes, $reason) {
            $id = Database::insert('interview_extension_requests', [
                'interview_id' => $interviewId,
                'requested_by' => $requestedBy,
                'requested_minutes' => $minutes,
                'request_reason' => $reason,
                'status' => InterviewExtensionStatus::PENDING->value,
                'requested_at' => date('Y-m-d H:i:s'),
            ]);

            InterviewAuditRepository::record($interviewId, $requestedBy, InterviewAuditAction::EXTENSION_REQUESTED->value, [
                'extension_request_id' => $id,
                'requested_minutes' => $minutes,
                'request_reason' => $reason,
            ]);

            return $id;
        });
    }

    public static function cancelExtension(int $interviewId, int $requestId, int $actorUserId): void
    {
        Database::transaction(function () use ($interviewId, $requestId, $actorUserId) {
            Database::update('interview_extension_requests', [
                'status' => InterviewExtensionStatus::CANCELLED->value,
                'cancelled_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ? AND extension_request_id = ? AND status = ?', [
                $interviewId,
                $requestId,
                InterviewExtensionStatus::PENDING->value,
            ]);

            InterviewAuditRepository::record($interviewId, $actorUserId, InterviewAuditAction::EXTENSION_CANCELLED->value, [
                'extension_request_id' => $requestId,
            ]);
        });
    }

    public static function approveExtension(int $interviewId, int $requestId, int $actorUserId, int $approvedMinutes, string $reason): void
    {
        Database::transaction(function () use ($interviewId, $requestId, $actorUserId, $approvedMinutes, $reason) {
            Database::update('interview_extension_requests', [
                'status' => InterviewExtensionStatus::APPROVED->value,
                'approved_minutes' => $approvedMinutes,
                'decided_by' => $actorUserId,
                'decision_reason' => $reason,
                'decided_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ? AND extension_request_id = ? AND status = ?', [
                $interviewId,
                $requestId,
                InterviewExtensionStatus::PENDING->value,
            ]);

            Database::update('interviews', [
                'extended_duration_minutes' => $approvedMinutes,
                'last_extension_decision_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ?', [$interviewId]);

            InterviewAuditRepository::record($interviewId, $actorUserId, InterviewAuditAction::EXTENSION_APPROVED->value, [
                'extension_request_id' => $requestId,
                'approved_minutes' => $approvedMinutes,
                'decision_reason' => $reason,
            ]);
        });
    }

    public static function denyExtension(int $interviewId, int $requestId, int $actorUserId, string $reason): void
    {
        Database::transaction(function () use ($interviewId, $requestId, $actorUserId, $reason) {
            Database::update('interview_extension_requests', [
                'status' => InterviewExtensionStatus::DENIED->value,
                'decided_by' => $actorUserId,
                'decision_reason' => $reason,
                'decided_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ? AND extension_request_id = ? AND status = ?', [
                $interviewId,
                $requestId,
                InterviewExtensionStatus::PENDING->value,
            ]);

            Database::update('interviews', [
                'last_extension_decision_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'interview_id = ?', [$interviewId]);

            InterviewAuditRepository::record($interviewId, $actorUserId, InterviewAuditAction::EXTENSION_DENIED->value, [
                'extension_request_id' => $requestId,
                'decision_reason' => $reason,
            ]);
        });
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

function str_limit_local(?string $value, int $limit): string
{
    $value = (string)$value;
    if (strlen($value) <= $limit) {
        return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
}
