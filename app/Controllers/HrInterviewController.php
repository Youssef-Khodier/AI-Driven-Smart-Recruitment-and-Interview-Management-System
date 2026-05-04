<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\InterviewAssignmentRole;
use App\Policies\InterviewPolicy;
use App\Repositories\InterviewRepository;

final class HrInterviewController extends Controller
{
    public function index(Request $request): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviews = InterviewRepository::hrInterviewList();

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
        $application = InterviewRepository::findEligibleApplicationForScheduling($applicationIdInt);

        if (!$application) {
            Session::flash('error', 'Application is not eligible for interview scheduling.');
            return $this->redirect(url('hr.requisitions.index'));
        }

        $panelUsers = InterviewRepository::activePanelUsers();

        return $this->view('hr/interviews/form', [
            'title' => 'Schedule Interview',
            'application' => $application,
            'panelUsers' => $panelUsers,
            'interview' => null,
            'roles' => InterviewAssignmentRole::values(),
        ]);
    }

    public function store(Request $request, string $applicationId): Response
    {
        $actor = $this->requireAuth();
        if (!(new InterviewPolicy())->manage($actor)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $applicationIdInt = (int)$applicationId;
        $application = InterviewRepository::findEligibleApplicationForScheduling($applicationIdInt);

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

        $activeUsers = array_column(InterviewRepository::activePanelUsers(), null, 'user_id');

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
            }
        }

        if (!$hasOfficialScorer) {
            throw new ValidationException(['panel_members' => ['At least one official scorer (Lead or Interviewer) is required.']]);
        }

        if (InterviewRepository::hasScheduleConflict(null, $applicationIdInt, $assignedUserIds, $data['scheduled_at'], $duration)) {
            throw new ValidationException(['scheduled_at' => ['Schedule conflict detected for the application or a panel member.']]);
        }

        $interviewId = InterviewRepository::createInterviewWithAssignments([
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
        $interview = InterviewRepository::findForHr($id);

        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->view($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interview['feedback'] = \App\Repositories\InterviewFeedbackRepository::forInterview($id);
        $interview['completion_state'] = \App\Repositories\InterviewFeedbackRepository::completionState($id);

        return $this->view('hr/interviews/show', [
            'title' => 'Interview Details',
            'interview' => $interview,
            'actor' => $actor,
        ]);
    }

    public function edit(Request $request, string $interviewId): Response
    {
        return new Response('Not implemented');
    }

    public function update(Request $request, string $interviewId): Response
    {
        return new Response('Not implemented');
    }

    public function cancel(Request $request, string $interviewId): Response
    {
        return new Response('Not implemented');
    }

    public function complete(Request $request, string $interviewId): Response
    {
        $actor = $this->requireAuth();
        $id = (int)$interviewId;
        $interview = InterviewRepository::findForHr($id);
        
        if (!$interview) {
            throw new \App\Core\HttpException(404, 'Interview not found.');
        }

        if (!(new InterviewPolicy())->complete($actor, $interview)) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }
        
        if ($interview['status'] !== \App\Enums\InterviewStatus::SCHEDULED->value) {
            throw new ValidationException(['status' => ['Interview must be SCHEDULED to complete.']]);
        }

        InterviewRepository::markCompleted($id, (int)$actor['user_id']);

        Session::flash('status', 'Interview marked as completed.');
        return $this->redirect(url('hr.interviews.show', [$id]));
    }

}
