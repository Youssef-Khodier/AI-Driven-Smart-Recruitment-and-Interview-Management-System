<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
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
        return $this->show($request, $id);
    }

    public function saveWorkspace(Request $request, string $id): Response
    {
        return new Response('Not implemented');
    }
}
