<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Repositories\InterviewRepository;

final class CandidateInterviewController extends Controller
{
    public function show(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewRepository::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('candidate/interviews/show', [
            'title' => 'My Interview',
            'interview' => $interview,
        ]);
    }

    public function workspace(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewRepository::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('interviews/workspace', [
            'title' => 'Coding Workspace',
            'interview' => $interview,
            'workspace' => InterviewRepository::workspaceForInterview($interviewId),
            'history' => InterviewRepository::workspaceHistory($interviewId),
            'saveRoute' => url('candidate.interviews.workspace.save', [$interviewId]),
            'backRoute' => url('candidate.interviews.show', [$interviewId]),
            'canSave' => true,
            'actorRole' => $actor['role'],
        ]);
    }

    public function saveWorkspace(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewRepository::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        InterviewRepository::saveWorkspaceSnapshot($interviewId, $request->body(), (int)$actor['user_id'], 'CANDIDATE');
        Session::flash('status', 'Workspace snapshot saved.');

        return $this->redirect(url('candidate.interviews.workspace', [$interviewId]));
    }
}
