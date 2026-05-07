<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Models\FeedbackGovernanceModel;
use App\Models\InterviewModel;
use App\Enums\FeedbackGovernanceAuditAction;

final class CandidateSentimentController extends Controller
{
    public function create(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewModel::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'You are not associated with this interview.');
        }

        if ($interview['status'] !== 'COMPLETED') {
            return $this->redirect(url('candidate.interviews.show', [$interviewId]))
                ->with('error', 'Sentiment can only be submitted after a completed interview.');
        }

        $alreadySubmitted = FeedbackGovernanceModel::hasSubmittedSentiment((int)$actor['user_id'], $interviewId);

        return $this->view('candidate/interviews/sentiment', [
            'title' => 'Post-Interview Feedback',
            'interview' => $interview,
            'alreadySubmitted' => $alreadySubmitted,
        ]);
    }

    public function store(Request $request, string $id): Response
    {
        $actor = $this->requireAuth();
        if ($actor['role'] !== 'CANDIDATE') {
            throw new \App\Core\HttpException(403, 'Unauthorized.');
        }

        $interviewId = (int)$id;
        $interview = InterviewModel::findForCandidate($interviewId, (int)$actor['user_id']);

        if (!$interview) {
            throw new \App\Core\HttpException(403, 'You are not associated with this interview.');
        }

        if ($interview['status'] !== 'COMPLETED') {
            return $this->redirect(url('candidate.interviews.show', [$interviewId]))
                ->with('error', 'Sentiment can only be submitted after a completed interview.');
        }

        if (FeedbackGovernanceModel::hasSubmittedSentiment((int)$actor['user_id'], $interviewId)) {
            return $this->redirect(url('candidate.interviews.show', [$interviewId]))
                ->with('error', 'You have already submitted your feedback for this interview.');
        }

        $data = $this->validate($request->body(), [
            'rating' => ['required', ['numeric']],
        ]);

        $rating = (int)$data['rating'];
        if ($rating < 1 || $rating > 5) {
            throw new \App\Core\ValidationException(['rating' => ['Rating must be between 1 and 5.']]);
        }

        $comment = trim($request->input('comment') ?? '');

        FeedbackGovernanceModel::createSentiment([
            'candidate_id' => (int)$actor['user_id'],
            'application_id' => (int)$interview['application_id'],
            'interview_id' => $interviewId,
            'rating' => $rating,
            'comment' => $comment ?: null,
        ]);

        FeedbackGovernanceModel::recordAudit([
            'actor_user_id' => (int)$actor['user_id'],
            'actor_role' => $actor['role'],
            'application_id' => (int)$interview['application_id'],
            'interview_id' => $interviewId,
            'entity_type' => 'candidate_interview_sentiment',
            'entity_id' => $interviewId,
            'action' => FeedbackGovernanceAuditAction::SENTIMENT_SUBMITTED->value,
            'new_values' => ['rating' => $rating],
        ]);

        Session::flash('status', 'Thank you for your feedback!');
        return $this->redirect(url('candidate.interviews.show', [$interviewId]));
    }
}
