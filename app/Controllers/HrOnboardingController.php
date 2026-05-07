<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Enums\OnboardingStatus;
use App\Enums\OfferStatus;
use App\Enums\PostOfferAuditAction;
use App\Policies\OnboardingPolicy;
use App\Models\OnboardingModel;
use App\Models\OfferModel;
use App\Models\PostOfferAuditModel;

final class HrOnboardingController extends Controller
{
    public function index(Request $request): Response
    {
        if (!OnboardingPolicy::viewAny()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $records = OnboardingModel::getList();

        return Response::view('hr/onboarding/index', [
            'title' => 'Onboarding',
            'records' => $records
        ]);
    }

    public function create(Request $request, int $offerId): Response
    {
        if (!OnboardingPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $offer = OfferModel::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::ACCEPTED->value) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Onboarding requires an accepted offer.');
        }

        $existing = OnboardingModel::findByOfferId($offerId);
        if ($existing) {
            return Response::redirect(url('hr.onboarding.show', [$existing['onboarding_id']]))->with('error', 'Onboarding already exists for this offer.');
        }

        return Response::view('hr/onboarding/form', [
            'title' => 'Create Onboarding',
            'offerId' => $offerId,
            'statuses' => OnboardingStatus::values(),
            'record' => null
        ]);
    }

    public function store(Request $request, int $offerId): Response
    {
        if (!OnboardingPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $offer = OfferModel::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::ACCEPTED->value) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Onboarding requires an accepted offer.');
        }

        if (OnboardingModel::findByOfferId($offerId)) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Onboarding already exists.');
        }

        $status = $request->input('status');
        if (!in_array($status, OnboardingStatus::values())) {
            return Response::redirect(url('hr.offers.onboarding.create', [$offerId]))->with('error', 'Invalid status.');
        }

        $startDate = $request->input('start_date') ?: null;
        $documentsCompleted = $request->boolean('documents_completed');
        $actorId = Auth::id();

        $onboardingId = OnboardingModel::create($offerId, $status, $startDate, $documentsCompleted, $actorId);

        PostOfferAuditModel::record($offer['application_id'], $offerId, $onboardingId, $actorId, PostOfferAuditAction::ONBOARDING_CREATE->value, [
            'status' => ['new' => $status],
            'start_date' => ['new' => $startDate],
            'documents_completed' => ['new' => $documentsCompleted],
        ]);

        return Response::redirect(url('hr.onboarding.show', [$onboardingId]))->with('success', 'Onboarding record created.');
    }

    public function show(Request $request, int $onboardingId): Response
    {
        if (!OnboardingPolicy::view()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $record = OnboardingModel::find($onboardingId);
        if (!$record) {
            return Response::redirect(url('hr.onboarding.index'))->with('error', 'Onboarding not found.');
        }

        return Response::view('hr/onboarding/show', [
            'title' => 'View Onboarding',
            'record' => $record,
            'statuses' => OnboardingStatus::values()
        ]);
    }

    public function update(Request $request, int $onboardingId): Response
    {
        if (!OnboardingPolicy::update()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $record = OnboardingModel::find($onboardingId);
        if (!$record) {
            return Response::redirect(url('hr.onboarding.index'))->with('error', 'Onboarding not found.');
        }

        $status = $request->input('status');
        if (!in_array($status, OnboardingStatus::values())) {
            return Response::redirect(url('hr.onboarding.show', [$onboardingId]))->with('error', 'Invalid status.');
        }

        $startDate = $request->input('start_date') ?: null;
        $documentsCompleted = $request->boolean('documents_completed');
        $actorId = Auth::id();

        OnboardingModel::update($onboardingId, $status, $startDate, $documentsCompleted);

        PostOfferAuditModel::record($record['application_id'], $record['offer_id'], $onboardingId, $actorId, PostOfferAuditAction::ONBOARDING_UPDATE->value, [
            'status' => ['old' => $record['status'], 'new' => $status],
            'start_date' => ['old' => $record['start_date'], 'new' => $startDate],
            'documents_completed' => ['old' => (bool)$record['documents_completed'], 'new' => $documentsCompleted],
        ]);

        return Response::redirect(url('hr.onboarding.show', [$onboardingId]))->with('success', 'Onboarding record updated.');
    }
}
