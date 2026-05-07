<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Enums\FinalEvaluationRecommendation;
use App\Policies\FinalEvaluationPolicy;
use App\Repositories\FinalEvaluationRepository;
use App\Repositories\FeedbackGovernanceRepository;
use App\Repositories\PostOfferAuditRepository;
use App\Enums\PostOfferAuditAction;
use App\Core\Database;

final class HrFinalEvaluationController extends Controller
{
    public function show(Request $request, int $id): Response
    {
        if (!FinalEvaluationPolicy::view()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $application = Database::fetch(
            'SELECT a.*, c.candidate_id, j.title as job_title, u.name as candidate_name 
             FROM applications a 
             JOIN candidates c ON a.candidate_id = c.candidate_id 
             JOIN users u ON c.candidate_id = u.user_id 
             JOIN job_requisitions j ON a.job_id = j.job_id 
             WHERE a.application_id = ?', 
            [$id]
        );

        if (!$application) {
            return Response::redirect(url('hr.requisitions.index'))->with('error', 'Application not found');
        }

        $evidence = FinalEvaluationRepository::getEvidence($id);
        $scoreData = FinalEvaluationRepository::calculateAggregateScore($evidence);
        $evaluation = FinalEvaluationRepository::findByApplicationId($id);
        $latestSnapshot = FeedbackGovernanceRepository::getLatestSnapshot($id);
        if ($latestSnapshot) {
            $scoreData['score'] = (float)$latestSnapshot['aggregate_score'];
            $scoreData['has_partial_evidence'] = $scoreData['has_partial_evidence'] || (int)$latestSnapshot['missing_feedback_count'] > 0;
        }

        $canCreateOffer = false;
        if ($evaluation && in_array($evaluation['recommendation'], [FinalEvaluationRecommendation::HIRE->value, FinalEvaluationRecommendation::STRONG_HIRE->value])) {
            $activeOffer = \App\Repositories\OfferRepository::getActiveOffer($id);
            $allOffers = \App\Repositories\OfferRepository::findByApplicationId($id);
            if (!$activeOffer && count($allOffers) < 2) {
                $canCreateOffer = true;
            }
        }

        return Response::view('hr/evaluations/show', [
            'title' => 'Final Evaluation',
            'application' => $application,
            'evidence' => $evidence,
            'scoreData' => $scoreData,
            'evaluation' => $evaluation,
            'latestSnapshot' => $latestSnapshot,
            'canCreateOffer' => $canCreateOffer,
            'recommendations' => FinalEvaluationRecommendation::values()
        ]);
    }

    public function store(Request $request, int $id): Response
    {
        if (!FinalEvaluationPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $evaluation = FinalEvaluationRepository::findByApplicationId($id);
        if ($evaluation) {
            return Response::redirect(url('hr.evaluations.show', [$id]))->with('error', 'Final evaluation already recorded.');
        }

        $evidence = FinalEvaluationRepository::getEvidence($id);
        $scoreData = FinalEvaluationRepository::calculateAggregateScore($evidence);
        $latestSnapshot = FeedbackGovernanceRepository::getLatestSnapshot($id);
        if ($latestSnapshot) {
            $scoreData['score'] = (float)$latestSnapshot['aggregate_score'];
            $scoreData['has_partial_evidence'] = $scoreData['has_partial_evidence'] || (int)$latestSnapshot['missing_feedback_count'] > 0;
        }

        if ($scoreData['has_partial_evidence'] && !$request->boolean('partial_evidence_acknowledged')) {
            return Response::redirect(url('hr.evaluations.show', [$id]))->with('error', 'You must acknowledge partial evidence to proceed.');
        }

        $recommendation = $request->input('recommendation');
        if (!in_array($recommendation, FinalEvaluationRecommendation::values())) {
            return Response::redirect(url('hr.evaluations.show', [$id]))->with('error', 'Invalid recommendation.');
        }

        $decisionNotes = trim($request->input('decision_notes') ?? '');
        if (empty($decisionNotes)) {
            return Response::redirect(url('hr.evaluations.show', [$id]))->with('error', 'Decision notes are required.');
        }

        $actorId = Auth::id();

        FinalEvaluationRepository::save(
            $id,
            $scoreData['score'],
            $recommendation,
            $decisionNotes,
            $scoreData['has_partial_evidence'],
            $actorId
        );

        PostOfferAuditRepository::record($id, null, null, $actorId, PostOfferAuditAction::FINAL_EVALUATION_SAVE->value, [
            'recommendation' => ['new' => $recommendation],
            'aggregate_score' => ['new' => $scoreData['score']],
            'partial_evidence_acknowledged' => ['new' => $scoreData['has_partial_evidence']],
        ]);

        return Response::redirect(url('hr.evaluations.show', [$id]))->with('success', 'Final evaluation saved.');
    }
}
