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
            'actor' => $actor,
        ]);
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

        $data['interview_id'] = $id;
        $data['interviewer_id'] = (int)$actor['user_id'];

        \App\Repositories\InterviewFeedbackRepository::create($data, (int)$actor['user_id']);

        Session::flash('status', 'Official feedback submitted successfully.');
        return $this->redirect(url('interviewer.interviews.show', [$id]));
    }

}
