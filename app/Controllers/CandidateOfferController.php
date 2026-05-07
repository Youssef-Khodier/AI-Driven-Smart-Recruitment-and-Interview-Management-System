<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Enums\OfferStatus;
use App\Enums\OnboardingStatus;
use App\Enums\PostOfferAuditAction;
use App\Policies\OfferPolicy;
use App\Models\OfferModel;
use App\Models\OnboardingModel;
use App\Models\PostOfferAuditModel;
use App\Core\Database;

final class CandidateOfferController extends Controller
{
    public function show(Request $request, int $offerId): Response
    {
        $actorId = Auth::id();
        OfferModel::enforceExpiryForOffer($offerId, $actorId);

        if (!OfferPolicy::view($offerId)) {
            return Response::redirect(url('candidate.dashboard'))->with('error', 'Unauthorized');
        }

        $offer = Database::fetch(
            'SELECT o.*, a.job_id, j.title as job_title, c.candidate_id, u.name as candidate_name 
             FROM offers o 
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             WHERE o.offer_id = ? AND o.status != ?',
            [$offerId, OfferStatus::DRAFT->value]
        );

        if (!$offer) {
            return Response::redirect(url('candidate.applications.index'))->with('error', 'Offer not found or not sent yet');
        }

        $onboarding = $offer['status'] === OfferStatus::ACCEPTED->value
            ? OnboardingModel::findByOfferId($offerId)
            : null;

        return Response::view('candidate/offers/show', [
            'title' => 'Your Offer',
            'offer' => $offer,
            'onboarding' => $onboarding,
        ]);
    }

    public function accept(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::respond($offerId)) {
            return Response::redirect(url('candidate.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        OfferModel::enforceExpiryForOffer($offerId, $actorId);

        $offer = OfferModel::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::SENT->value) {
            return Response::redirect(url('candidate.offers.show', [$offerId]))->with('error', 'Offer is no longer available for acceptance.');
        }

        OfferModel::accept($offerId, $actorId);

        $onboarding = OnboardingModel::findByOfferId($offerId);
        if (!$onboarding) {
            $onboardingId = OnboardingModel::create($offerId, OnboardingStatus::PENDING->value, null, false, $actorId);
            PostOfferAuditModel::record($offer['application_id'], $offerId, $onboardingId, $actorId, PostOfferAuditAction::ONBOARDING_CREATE->value, [
                'status' => ['new' => OnboardingStatus::PENDING->value],
                'source' => ['new' => 'candidate_offer_acceptance'],
            ]);
        }

        PostOfferAuditModel::record($offer['application_id'], $offerId, null, $actorId, PostOfferAuditAction::OFFER_ACCEPT->value, [
            'status' => ['old' => OfferStatus::SENT->value, 'new' => OfferStatus::ACCEPTED->value]
        ]);

        return Response::redirect(url('candidate.offers.show', [$offerId]))->with('success', 'You have accepted the offer.');
    }

    public function reject(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::respond($offerId)) {
            return Response::redirect(url('candidate.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        OfferModel::enforceExpiryForOffer($offerId, $actorId);

        $offer = OfferModel::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::SENT->value) {
            return Response::redirect(url('candidate.offers.show', [$offerId]))->with('error', 'Offer is no longer available for rejection.');
        }

        OfferModel::reject($offerId, $actorId);

        PostOfferAuditModel::record($offer['application_id'], $offerId, null, $actorId, PostOfferAuditAction::OFFER_REJECT->value, [
            'status' => ['old' => OfferStatus::SENT->value, 'new' => OfferStatus::REJECTED->value]
        ]);

        return Response::redirect(url('candidate.offers.show', [$offerId]))->with('success', 'You have rejected the offer.');
    }
}
