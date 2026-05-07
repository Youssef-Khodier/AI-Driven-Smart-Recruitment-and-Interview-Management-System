<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Policies\InterviewPolicy;
use App\Repositories\InterviewRepository;

final class InterviewerInterviewController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $this->requireAuth();
        $interviews = InterviewRepository::assignedInterviewList((int)$actor['user_id']);

        return $this->view('interviewer/interviews/index', [
            'title' => 'Assigned Interviews',
            'interviews' => $interviews,
            'actor' => $actor,
        ]);
    }

    public function show(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        
        $briefing = InterviewRepository::briefingForAssignedUser((int)$interviewId, (int)$actor['user_id']);

        if (!$briefing) {
            throw new \App\Core\HttpException(403, 'Unauthorized. You are not assigned to this interview.');
        }

        if (!(new InterviewPolicy())->view($actor, ['interview_id' => $interviewId])) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('interviewer/interviews/show', [
            'title' => 'Interview Briefing',
            'briefing' => $briefing,
            'workspace' => InterviewRepository::workspaceForInterview((int)$interviewId),
            'extensionRequests' => InterviewRepository::extensionRequests((int)$interviewId),
            'actor' => $actor,
        ]);
    }

    public function workspace(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $briefing = InterviewRepository::briefingForAssignedUser($id, (int)$actor['user_id']);

        if (!$briefing) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $canSave = (new InterviewPolicy())->updateWorkspace($actor, $briefing);

        return $this->view('interviews/workspace', [
            'title' => 'Coding Workspace',
            'interview' => $briefing,
            'workspace' => InterviewRepository::workspaceForInterview($id),
            'history' => InterviewRepository::workspaceHistory($id),
            'saveRoute' => url('interviewer.interviews.workspace.save', [$id]),
            'backRoute' => url('interviewer.interviews.show', [$id]),
            'canSave' => $canSave,
            'actorRole' => $actor['role'],
        ]);
    }

    public function saveWorkspace(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $briefing = InterviewRepository::briefingForAssignedUser($id, (int)$actor['user_id']);

        if (!$briefing) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        if (!(new InterviewPolicy())->updateWorkspace($actor, $briefing)) {
            throw new \App\Core\HttpException(403, 'Shadow observers can view the workspace but cannot save changes.');
        }

        InterviewRepository::saveWorkspaceSnapshot($id, $request->body(), (int)$actor['user_id'], 'INTERVIEWER');
        Session::flash('status', 'Workspace snapshot saved.');

        return $this->redirect(url('interviewer.interviews.workspace', [$id]));
    }

    public function requestExtension(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $briefing = InterviewRepository::briefingForAssignedUser($id, (int)$actor['user_id']);

        if (!$briefing || !(new InterviewPolicy())->requestExtension($actor, $briefing)) {
            throw new \App\Core\HttpException(403, 'Only assigned official interviewers can request extensions.');
        }

        $data = $this->validate($request->body(), [
            'requested_minutes' => ['required'],
            'request_reason' => ['required'],
        ]);

        $minutes = (int)$data['requested_minutes'];
        if ($minutes <= 0) {
            throw new \App\Core\ValidationException(['requested_minutes' => ['Requested duration must be positive.']]);
        }

        InterviewRepository::requestExtension($id, (int)$actor['user_id'], $minutes, $data['request_reason']);
        Session::flash('status', 'Extension request sent to HR.');

        return $this->redirect(url('interviewer.interviews.show', [$id]));
    }

    public function cancelExtension(Request $request, string $interviewId, string $requestId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $extension = InterviewRepository::findExtensionRequest($id, (int)$requestId);

        if (!$extension || (int)$extension['requested_by'] !== (int)$actor['user_id']) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        InterviewRepository::cancelExtension($id, (int)$requestId, (int)$actor['user_id']);
        Session::flash('status', 'Extension request cancelled.');

        return $this->redirect(url('interviewer.interviews.show', [$id]));
    }

    public function feedback(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        
        $briefing = InterviewRepository::briefingForAssignedUser($id, (int)$actor['user_id']);

        if (!$briefing) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($id, (int)$actor['user_id']);

        if (!(new \App\Policies\InterviewFeedbackPolicy())->create($actor, $briefing, $briefing['assignment'], $alreadySubmitted)) {
            throw new \App\Core\HttpException(403, 'You cannot submit official feedback for this interview.');
        }

        return $this->view('interviewer/interviews/feedback', [
            'title' => 'Submit Official Feedback',
            'briefing' => $briefing,
        ]);
    }

    public function storeFeedback(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        
        $briefing = InterviewRepository::briefingForAssignedUser($id, (int)$actor['user_id']);

        if (!$briefing) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $alreadySubmitted = \App\Repositories\InterviewFeedbackRepository::alreadySubmitted($id, (int)$actor['user_id']);

        if (!(new \App\Policies\InterviewFeedbackPolicy())->create($actor, $briefing, $briefing['assignment'], $alreadySubmitted)) {
            throw new \App\Core\HttpException(403, 'You cannot submit official feedback for this interview.');
        }

        $data = $this->validate($request->body(), [
            'technical_score' => ['required', ['numeric']],
            'communication_score' => ['required', ['numeric']],
            'culture_fit_score' => ['required', ['numeric']],
            'overall_score' => ['required', ['numeric']],
            'comments' => ['required'],
        ]);

        foreach (['technical_score', 'communication_score', 'culture_fit_score', 'overall_score'] as $field) {
            $val = (float)$data[$field];
            if ($val < 0 || $val > 10) {
                throw new \App\Core\ValidationException([$field => ['Score must be between 0 and 10.']]);
            }
            $data[$field] = $val;
        }

        $flagPayload = null;
        if ($request->boolean('has_red_flag')) {
            $flagData = $this->validate($request->body(), [
                'red_flag_category' => ['required'],
                'red_flag_severity' => ['required'],
                'red_flag_explanation' => ['required'],
            ]);

            $severity = $flagData['red_flag_severity'];
            if (!in_array($severity, ['LOW', 'MEDIUM', 'HIGH'], true)) {
                throw new \App\Core\ValidationException(['red_flag_severity' => ['Invalid red flag severity.']]);
            }

            $flagPayload = [
                'category' => trim($flagData['red_flag_category']),
                'severity' => $severity,
                'explanation' => trim($flagData['red_flag_explanation']),
            ];
        }

        $data['interview_id'] = $id;
        $data['interviewer_id'] = (int)$actor['user_id'];

        \App\Repositories\InterviewFeedbackRepository::create($data, (int)$actor['user_id']);

        if ($flagPayload) {
            $flagId = \App\Repositories\FeedbackGovernanceRepository::createConcernFlag([
                'application_id' => (int)$briefing['application_id'],
                'interview_id' => $id,
                'candidate_id' => (int)$briefing['candidate_id'],
                'category' => $flagPayload['category'],
                'severity' => $flagPayload['severity'],
                'explanation' => $flagPayload['explanation'],
                'created_by' => (int)$actor['user_id'],
            ]);

            \App\Repositories\FeedbackGovernanceRepository::recordAudit([
                'actor_user_id' => (int)$actor['user_id'],
                'actor_role' => $actor['role'],
                'application_id' => (int)$briefing['application_id'],
                'interview_id' => $id,
                'entity_type' => 'feedback_concern_flags',
                'entity_id' => $flagId,
                'action' => \App\Enums\FeedbackGovernanceAuditAction::CONCERN_FLAG_CREATED->value,
                'new_values' => [
                    'category' => $flagPayload['category'],
                    'severity' => $flagPayload['severity'],
                ],
            ]);
        }

        \App\Repositories\FeedbackGovernanceRepository::refreshForInterview($id, (int)$actor['user_id'], $actor['role']);

        Session::flash('status', 'Official feedback submitted successfully.');
        return $this->redirect(url('interviewer.interviews.show', [$id]));
    }

}
