<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\HttpException;
use App\Enums\UserRole;
use App\Enums\FeedbackConcernStatus;
use App\Enums\FeedbackGovernanceAuditAction;
use App\Policies\ReportPolicy;
use App\Models\FeedbackGovernanceModel;
use App\Core\Database;

final class HrFeedbackGovernanceController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        if (!(new ReportPolicy())->viewFeedbackGovernance($user)) {
            throw new HttpException(403, 'Unauthorized.');
        }

        $openFlags = Database::fetchAll(
            'SELECT f.*, u.name AS candidate_name, i.interview_id, j.title AS job_title
             FROM feedback_concern_flags f
             JOIN candidates c ON f.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             LEFT JOIN interviews i ON f.interview_id = i.interview_id
             LEFT JOIN applications a ON f.application_id = a.application_id
             LEFT JOIN job_requisitions j ON a.job_id = j.job_id
             WHERE f.status = ?
             ORDER BY f.created_at DESC',
            [FeedbackConcernStatus::OPEN->value]
        );

        $recentSnapshots = Database::fetchAll(
            'SELECT n.*, u.name AS candidate_name, j.title AS job_title
             FROM normalized_evaluation_snapshots n
             JOIN applications a ON n.application_id = a.application_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             ORDER BY n.created_at DESC LIMIT 20'
        );

        return $this->view('hr/governance/feedback-governance', [
            'title' => 'Feedback Governance',
            'openFlags' => $openFlags,
            'recentSnapshots' => $recentSnapshots,
        ]);
    }

    public function show(Request $request, int $applicationId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);

        $application = Database::fetch(
            'SELECT a.*, j.title AS job_title, j.job_id, u.name AS candidate_name
             FROM applications a
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN users u ON a.candidate_id = u.user_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            throw new HttpException(404, 'Application not found.');
        }

        $flags = Database::fetchAll(
            'SELECT f.*, cu.name AS created_by_name, ru.name AS resolved_by_name
             FROM feedback_concern_flags f
             JOIN users cu ON f.created_by = cu.user_id
             LEFT JOIN users ru ON f.resolved_by = ru.user_id
             WHERE f.application_id = ?
             ORDER BY f.created_at DESC',
            [$applicationId]
        );

        $snapshots = Database::fetchAll(
            'SELECT * FROM normalized_evaluation_snapshots
             WHERE application_id = ?
             ORDER BY created_at DESC',
            [$applicationId]
        );

        $debriefs = Database::fetchAll(
            'SELECT d.*, cu.name AS completed_by_name
             FROM evaluation_debrief_records d
             LEFT JOIN users cu ON d.completed_by = cu.user_id
             WHERE d.application_id = ?
             ORDER BY d.created_at DESC',
            [$applicationId]
        );

        $sentiments = Database::fetchAll(
            'SELECT s.*, u.name AS candidate_name
             FROM candidate_interview_sentiment s
             JOIN users u ON s.candidate_id = u.user_id
             WHERE s.application_id = ?
             ORDER BY s.submitted_at DESC',
            [$applicationId]
        );

        $gapSnapshots = [];
        if (!empty($snapshots)) {
            $latestSnapshotId = $snapshots[0]['snapshot_id'];
            $gapSnapshots = FeedbackGovernanceModel::getGapSnapshots($latestSnapshotId);
        }

        $benchmarks = FeedbackGovernanceModel::getBenchmarksForJob((int)$application['job_id']);

        return $this->view('hr/governance/feedback-detail', [
            'title' => 'Feedback Governance — ' . $application['candidate_name'],
            'application' => $application,
            'flags' => $flags,
            'snapshots' => $snapshots,
            'debriefs' => $debriefs,
            'sentiments' => $sentiments,
            'gapSnapshots' => $gapSnapshots,
            'benchmarks' => $benchmarks,
            'resolutionStatuses' => FeedbackConcernStatus::values(),
        ]);
    }

    public function resolveFlag(Request $request, int $flagId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        $actorId = (int)$user['user_id'];

        $flag = Database::fetch('SELECT * FROM feedback_concern_flags WHERE flag_id = ?', [$flagId]);
        if (!$flag) {
            throw new HttpException(404, 'Flag not found.');
        }

        if ($flag['status'] !== FeedbackConcernStatus::OPEN->value) {
            return $this->redirect(url('hr.governance.show', [$flag['application_id']]))
                ->with('error', 'This flag has already been resolved.');
        }

        $data = $this->validate($request->body(), [
            'resolution_status' => ['required'],
            'resolution_rationale' => ['required'],
        ]);

        $resolutionStatus = $data['resolution_status'];
        $validStatuses = array_filter(FeedbackConcernStatus::values(), fn($s) => $s !== FeedbackConcernStatus::OPEN->value);
        if (!in_array($resolutionStatus, $validStatuses)) {
            throw new \App\Core\ValidationException(['resolution_status' => ['Invalid resolution status.']]);
        }

        FeedbackGovernanceModel::resolveConcernFlag($flagId, [
            'status' => $resolutionStatus,
            'resolved_by' => $actorId,
            'resolution_rationale' => $data['resolution_rationale'],
        ]);

        FeedbackGovernanceModel::recordAudit([
            'actor_user_id' => $actorId,
            'actor_role' => $user['role'],
            'application_id' => (int)$flag['application_id'],
            'interview_id' => $flag['interview_id'] ? (int)$flag['interview_id'] : null,
            'entity_type' => 'feedback_concern_flags',
            'entity_id' => $flagId,
            'action' => FeedbackGovernanceAuditAction::CONCERN_FLAG_RESOLVED->value,
            'old_values' => ['status' => FeedbackConcernStatus::OPEN->value],
            'new_values' => ['status' => $resolutionStatus, 'rationale' => $data['resolution_rationale']],
        ]);

        return $this->redirect(url('hr.governance.show', [$flag['application_id']]))
            ->with('success', 'Concern flag resolved.');
    }
}
