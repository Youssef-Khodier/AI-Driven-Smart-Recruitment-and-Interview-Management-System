<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\InterviewModel;

final class CandidateInterviewController extends Controller
{
    public function show(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewModel::findForCandidate($interviewId, (int)$actor['user_id']);

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
        $interview = InterviewModel::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        return $this->view('interviews/workspace', [
            'title' => 'Coding Workspace',
            'interview' => $interview,
            'workspace' => InterviewModel::workspaceForInterview($interviewId),
            'history' => InterviewModel::workspaceHistory($interviewId),
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
        $interview = InterviewModel::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        InterviewModel::saveWorkspaceSnapshot($interviewId, $request->body(), (int)$actor['user_id'], 'CANDIDATE');
        Session::flash('status', 'Workspace snapshot saved.');

        return $this->redirect(url('candidate.interviews.workspace', [$interviewId]));
    }
}
