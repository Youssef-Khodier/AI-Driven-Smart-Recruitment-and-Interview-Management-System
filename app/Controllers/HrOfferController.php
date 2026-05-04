<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Enums\OfferType;
use App\Enums\OfferStatus;
use App\Enums\FinalEvaluationRecommendation;
use App\Enums\PostOfferAuditAction;
use App\Policies\OfferPolicy;
use App\Repositories\OfferRepository;
use App\Repositories\FinalEvaluationRepository;
use App\Repositories\PostOfferAuditRepository;
use App\Core\Database;

final class HrOfferController extends Controller
{
    public function index(Request $request): Response
    {
        if (!OfferPolicy::viewAny()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        $offers = OfferRepository::getOffersWithDetails();
        
        $expiredAny = false;
        foreach ($offers as $o) {
            if ($o['status'] === OfferStatus::SENT->value && strtotime($o['expiry_date']) < time()) {
                OfferRepository::enforceExpiryForOffer((int)$o['offer_id'], $actorId);
                $expiredAny = true;
            }
        }
        
        if ($expiredAny) {
            $offers = OfferRepository::getOffersWithDetails(); // fetch again to get updated statuses
        }

        return Response::view('hr/offers/index', [
            'title' => 'Offers',
            'offers' => $offers
        ]);
    }

    public function create(Request $request, int $applicationId): Response
    {
        if (!OfferPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $evaluation = FinalEvaluationRepository::findByApplicationId($applicationId);
        if (!$evaluation || !in_array($evaluation['recommendation'], [FinalEvaluationRecommendation::HIRE->value, FinalEvaluationRecommendation::STRONG_HIRE->value])) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Application is not offer-eligible.');
        }

        $actorId = Auth::id();
        $activeOffer = OfferRepository::getActiveOffer($applicationId);
        if ($activeOffer && $activeOffer['status'] === OfferStatus::SENT->value) {
            OfferRepository::enforceExpiryForOffer((int)$activeOffer['offer_id'], $actorId);
        }

        if (OfferRepository::getActiveOffer($applicationId)) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'An active offer already exists.');
        }

        $existingOffers = OfferRepository::findByApplicationId($applicationId);
        if (count($existingOffers) >= 2) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Maximum number of offers reached.');
        }

        $replacesOfferId = count($existingOffers) > 0 ? $existingOffers[0]['offer_id'] : null;

        return Response::view('hr/offers/form', [
            'title' => 'Create Offer',
            'applicationId' => $applicationId,
            'replacesOfferId' => $replacesOfferId,
            'offerTypes' => OfferType::values(),
        ]);
    }

    public function store(Request $request, int $applicationId): Response
    {
        if (!OfferPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }
        
        $actorId = Auth::id();
        $activeOffer = OfferRepository::getActiveOffer($applicationId);
        if ($activeOffer && $activeOffer['status'] === OfferStatus::SENT->value) {
            OfferRepository::enforceExpiryForOffer((int)$activeOffer['offer_id'], $actorId);
        }

        if (OfferRepository::getActiveOffer($applicationId)) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'An active offer already exists.');
        }
        
        $existingOffers = OfferRepository::findByApplicationId($applicationId);
        if (count($existingOffers) >= 2) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Maximum number of offers reached.');
        }

        $type = $request->input('offer_type');
        if (!in_array($type, OfferType::values())) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Invalid offer type.');
        }

        $ctc = (float)$request->input('ctc');
        $bonus = (float)($request->input('bonus') ?: 0);
        $stock = (float)($request->input('stock_options') ?: 0);
        
        if ($ctc < 0 || $bonus < 0 || $stock < 0) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Compensation amounts must be non-negative.');
        }

        $expiryDate = $request->input('expiry_date');
        if (strtotime($expiryDate) < time()) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Expiry date must be in the future.');
        }

        $replacesOfferId = count($existingOffers) > 0 ? $existingOffers[0]['offer_id'] : null;

        $offerId = OfferRepository::createDraft($applicationId, $type, $ctc, $bonus, $stock, $expiryDate, $actorId, $replacesOfferId);

        $action = $replacesOfferId ? PostOfferAuditAction::OFFER_REPLACE->value : PostOfferAuditAction::OFFER_CREATE->value;

        PostOfferAuditRepository::record($applicationId, $offerId, null, $actorId, $action, [
            'status' => ['new' => OfferStatus::DRAFT->value],
            'ctc' => ['new' => $ctc]
        ]);

        return Response::redirect(url('hr.offers.show', [$offerId]))->with('success', 'Draft offer created.');
    }

    public function show(Request $request, int $offerId): Response
    {
        $actorId = Auth::id();
        OfferRepository::enforceExpiryForOffer($offerId, $actorId);

        if (!OfferPolicy::view($offerId)) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $offer = Database::fetch(
            'SELECT o.*, a.job_id, j.title as job_title, c.candidate_id, u.name as candidate_name 
             FROM offers o 
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             WHERE o.offer_id = ?',
            [$offerId]
        );

        if (!$offer) {
            return Response::redirect(url('hr.offers.index'))->with('error', 'Offer not found');
        }

        $onboarding = null;
        if ($offer['status'] === OfferStatus::ACCEPTED->value) {
            $onboarding = \App\Repositories\OnboardingRepository::findByOfferId($offerId);
        }

        return Response::view('hr/offers/show', [
            'title' => 'View Offer',
            'offer' => $offer,
            'onboarding' => $onboarding
        ]);
    }

    public function send(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::send()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        OfferRepository::enforceExpiryForOffer($offerId, $actorId);

        $offer = OfferRepository::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::DRAFT->value) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Only draft offers can be sent.');
        }

        if (strtotime($offer['expiry_date']) < time()) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Cannot send an offer with a past expiry date.');
        }

        OfferRepository::send($offerId, $actorId);

        PostOfferAuditRepository::record($offer['application_id'], $offerId, null, $actorId, PostOfferAuditAction::OFFER_SEND->value, [
            'status' => ['old' => OfferStatus::DRAFT->value, 'new' => OfferStatus::SENT->value]
        ]);

        return Response::redirect(url('hr.offers.show', [$offerId]))->with('success', 'Offer sent to candidate.');
    }
}