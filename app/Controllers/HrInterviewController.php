<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\InterviewAssignmentRole;
use App\Policies\InterviewPolicy;
use App\Models\InterviewModel;

final class HrInterviewController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviews = InterviewModel::hrInterviewList();

        return $this->view('hr/interviews/index', [
            'title' => 'Interviews',
            'interviews' => $interviews,
        ]);
    }

    public function create(Request $request, string $applicationId): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $applicationIdInt = (int)$applicationId;
        $application = InterviewModel::findEligibleApplicationForScheduling($applicationIdInt);

        if (!$application) {
            Session::flash('error', 'Application is not eligible for interview scheduling.');
            return $this->redirect(url('hr.requisitions.index'));
        }

        $panelUsers = InterviewModel::activePanelUsers();
        $recommendation = Session::flashed('panel_recommendation');

        return $this->view('hr/interviews/form', [
            'title' => 'Schedule Interview',
            'application' => $application,
            'panelUsers' => $panelUsers,
            'recommendation' => $recommendation,
            'interview' => null,
            'roles' => InterviewAssignmentRole::values(),
        ]);
    }

    public function recommendPanel(Request $request, string $applicationId): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->recommendPanel($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $data = $this->validate($request->body(), [
            'scheduled_at' => ['required'],
            'duration_minutes' => ['required'],
        ]);

        $duration = (int)$data['duration_minutes'];
        if ($duration <= 0) {
            throw new ValidationException(['duration_minutes' => ['Duration must be a positive number.']]);
        }

        $recommendation = InterviewModel::recommendedPanel((int)$applicationId, $data['scheduled_at'], $duration, (int)$actor['user_id']);
        Session::flash('panel_recommendation', $recommendation);
        Session::flash('old', $request->body());
        Session::flash('status', 'Panel recommendation generated from current assignment counts.');

        return $this->redirect(url('hr.interviews.create', [$applicationId]));
    }

    public function store(Request $request, string $applicationId): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $applicationIdInt = (int)$applicationId;
        $application = InterviewModel::findEligibleApplicationForScheduling($applicationIdInt);

        if (!$application) {
            throw new ValidationException(['application_id' => ['Application is not eligible.']]);
        }

        $data = $this->validate($request->body(), [
            'interview_type' => ['required', ['in', ['TECHNICAL', 'HR', 'PANEL']]],
            'scheduled_at' => ['required'],
            'duration_minutes' => ['required'],
            'panel_members' => [],
        ]);

        if (strtotime($data['scheduled_at']) <= time()) {
            throw new ValidationException(['scheduled_at' => ['Scheduled time must be in the future.']]);
        }

        $duration = (int) $data['duration_minutes'];
        if ($duration <= 0) {
            throw new ValidationException(['duration_minutes' => ['Duration must be a positive number.']]);
        }

        // Normalize panel input
        $rawPanel = $request->body()['panel_members'] ?? [];
        $assignments = [];
        $assignedUserIds = [];
        $hasOfficialScorer = false;
        $hasHrRepresentative = false;
        $hasTechnicalInterviewer = false;

        $activeUsers = array_column(InterviewModel::activePanelUsers(), null, 'user_id');

        if (is_array($rawPanel)) {
            foreach ($rawPanel as $member) {
                if (empty($member['user_id'])) {
                    continue; // Skip blank rows
                }
                
                $userId = (int) $member['user_id'];
                $role = $member['role_in_panel'] ?? '';

                if (in_array($userId, $assignedUserIds)) {
                    throw new ValidationException(['panel_members' => ['Duplicate user assignment.']]);
                }

                if (!isset($activeUsers[$userId])) {
                    throw new ValidationException(['panel_members' => ['Assigned user is not active or not allowed.']]);
                }

                if (!in_array($role, InterviewAssignmentRole::values())) {
                    throw new ValidationException(['panel_members' => ['Invalid panel role.']]);
                }

                $assignedUserIds[] = $userId;
                $assignments[] = [
                    'interviewer_id' => $userId,
                    'role_in_panel' => $role,
                    'is_shadowing' => $role === InterviewAssignmentRole::OBSERVER->value,
                ];

                if (in_array($role, InterviewAssignmentRole::officialScorerValues())) {
                    $hasOfficialScorer = true;
                }
                if ($role === InterviewAssignmentRole::HR_REPRESENTATIVE->value) {
                    $hasHrRepresentative = true;
                }
                if (in_array($role, [InterviewAssignmentRole::PANEL_LEAD->value, InterviewAssignmentRole::INTERVIEWER->value])) {
                    $hasTechnicalInterviewer = true;
                }
            }
        }

        if (!$hasOfficialScorer) {
            throw new ValidationException(['panel_members' => ['At least one official scorer (Lead or Interviewer) is required.']]);
        }
        if (!$hasHrRepresentative || !$hasTechnicalInterviewer) {
            throw new ValidationException(['panel_members' => ['Panel must include an HR representative and a technical interviewer. Shadow observer is optional.']]);
        }

        if (InterviewModel::hasScheduleConflict(null, $applicationIdInt, $assignedUserIds, $data['scheduled_at'], $duration)) {
            throw new ValidationException(['scheduled_at' => ['Schedule conflict detected for the application or a panel member.']]);
        }

        $interviewId = InterviewModel::createInterviewWithAssignments([
            'application_id' => $applicationIdInt,
            'interview_type' => $data['interview_type'],
            'scheduled_at' => $data['scheduled_at'],
            'duration_minutes' => $duration,
        ], $assignments, (int)$actor['user_id']);

        Session::flash('status', 'Interview scheduled.');
        return $this->redirect(url('hr.interviews.show', [$interviewId]));
    }

    public function show(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->view($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interview['feedback'] = \App\Models\InterviewFeedbackModel::forInterview($id);
        $interview['completion_state'] = \App\Models\InterviewFeedbackModel::completionState($id);
        $interview['briefing_snapshot'] = InterviewModel::briefingSnapshot($id);
        $interview['extension_requests'] = InterviewModel::extensionRequests($id);
        $interview['workspace'] = InterviewModel::workspaceForInterview($id);

        return $this->view('hr/interviews/show', [
            'title' => 'Interview Details',
            'interview' => $interview,
            'actor' => $actor,
        ]);
    }

    public function refreshBriefing(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        InterviewModel::refreshBriefingSnapshot($id, (int)$actor['user_id']);
        Session::flash('status', 'Briefing snapshot refreshed.');

        return $this->redirect(url('hr.interviews.show', [$id]));
    }

    public function workspace(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->manageWorkspace($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('interviews/workspace', [
            'title' => 'Coding Workspace',
            'interview' => $interview,
            'workspace' => InterviewModel::workspaceForInterview($id),
            'history' => InterviewModel::workspaceHistory($id),
            'saveRoute' => url('hr.interviews.workspace.save', [$id]),
            'backRoute' => url('hr.interviews.show', [$id]),
            'canSave' => true,
            'actorRole' => $actor['role'],
        ]);
    }

    public function saveWorkspace(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->manageWorkspace($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        InterviewModel::saveWorkspaceSnapshot($id, $request->body(), (int)$actor['user_id'], 'HR');
        Session::flash('status', 'Workspace snapshot saved.');

        return $this->redirect(url('hr.interviews.workspace', [$id]));
    }

    public function showExtension(Request $request, string $interviewId, string $requestId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);
        $extension = InterviewModel::findExtensionRequest($id, (int)$requestId);

        if (!$interview || !$extension) {
            throw new \App\Core\HttpException(404, 'Extension request not found.');
        }

        if (!(new InterviewPolicy())->decideExtension($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('hr/interviews/extension', [
            'title' => 'Extension Request',
            'interview' => $interview,
            'extension' => $extension,
        ]);
    }

    public function approveExtension(Request $request, string $interviewId, string $requestId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview || !(new InterviewPolicy())->decideExtension($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $data = $this->validate($request->body(), [
            'approved_minutes' => ['required'],
            'decision_reason' => ['required'],
        ]);

        $approvedMinutes = (int)$data['approved_minutes'];
        if ($approvedMinutes <= 0) {
            throw new ValidationException(['approved_minutes' => ['Approved duration must be positive.']]);
        }

        InterviewModel::approveExtension($id, (int)$requestId, (int)$actor['user_id'], $approvedMinutes, $data['decision_reason']);
        Session::flash('status', 'Extension approved and interview duration updated.');

        return $this->redirect(url('hr.interviews.show', [$id]));
    }

    public function denyExtension(Request $request, string $interviewId, string $requestId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);

        if (!$interview || !(new InterviewPolicy())->decideExtension($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $data = $this->validate($request->body(), [
            'decision_reason' => ['required'],
        ]);

        InterviewModel::denyExtension($id, (int)$requestId, (int)$actor['user_id'], $data['decision_reason']);
        Session::flash('status', 'Extension denied.');

        return $this->redirect(url('hr.interviews.show', [$id]));
    }

    public function audit(Request $request, string $interviewId): Response
    {
        return $this->show($request, $interviewId);
    }

    public function complete(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewModel::findForHr($id);
        
        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->complete($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }
        
        if ($interview['status'] !== \App\Enums\InterviewStatus::SCHEDULED->value) {
            throw new ValidationException(['status' => ['Interview must be SCHEDULED to complete.']]);
        }

        InterviewModel::markCompleted($id, (int)$actor['user_id']);

        Session::flash('status', 'Interview marked as completed.');
        return $this->redirect(url('hr.interviews.show', [$id]));
    }

}
