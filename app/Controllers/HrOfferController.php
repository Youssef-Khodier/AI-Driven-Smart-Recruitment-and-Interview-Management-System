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
use App\Models\OfferModel;
use App\Models\FinalEvaluationModel;
use App\Models\PostOfferAuditModel;
use App\Services\OfferPackageCalculator;
use App\Core\Database;

final class HrOfferController extends Controller
{
    public function index(Request $request): Response
    {
        if (!OfferPolicy::viewAny()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        $offers = OfferModel::getOffersWithDetails();
        
        $expiredAny = false;
        foreach ($offers as $o) {
            if ($o['status'] === OfferStatus::SENT->value && strtotime($o['expiry_date']) < time()) {
                OfferModel::enforceExpiryForOffer((int)$o['offer_id'], $actorId);
                $expiredAny = true;
            }
        }
        
        if ($expiredAny) {
            $offers = OfferModel::getOffersWithDetails(); // fetch again to get updated statuses
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

        $evaluation = FinalEvaluationModel::findByApplicationId($applicationId);
        if (!$evaluation || !in_array($evaluation['recommendation'], [FinalEvaluationRecommendation::HIRE->value, FinalEvaluationRecommendation::STRONG_HIRE->value])) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Application is not offer-eligible.');
        }

        $actorId = Auth::id();
        $activeOffer = OfferModel::getActiveOffer($applicationId);
        if ($activeOffer && $activeOffer['status'] === OfferStatus::SENT->value) {
            OfferModel::enforceExpiryForOffer((int)$activeOffer['offer_id'], $actorId);
        }

        if (OfferModel::getActiveOffer($applicationId)) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'An active offer already exists.');
        }

        $existingOffers = OfferModel::findByApplicationId($applicationId);
        if (count($existingOffers) >= 2) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Maximum number of offers reached.');
        }

        $replacesOfferId = count($existingOffers) > 0 ? $existingOffers[0]['offer_id'] : null;

        $application = Database::fetch(
            'SELECT a.*, c.years_experience, j.title AS job_title
             FROM applications a
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        return Response::view('hr/offers/form', [
            'title' => 'Create Offer',
            'applicationId' => $applicationId,
            'replacesOfferId' => $replacesOfferId,
            'offerTypes' => OfferType::values(),
            'application' => $application,
        ]);
    }

    public function store(Request $request, int $applicationId): Response
    {
        if (!OfferPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }
        
        $actorId = Auth::id();
        $activeOffer = OfferModel::getActiveOffer($applicationId);
        if ($activeOffer && $activeOffer['status'] === OfferStatus::SENT->value) {
            OfferModel::enforceExpiryForOffer((int)$activeOffer['offer_id'], $actorId);
        }

        if (OfferModel::getActiveOffer($applicationId)) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'An active offer already exists.');
        }
        
        $existingOffers = OfferModel::findByApplicationId($applicationId);
        if (count($existingOffers) >= 2) {
            return Response::redirect(url('hr.evaluations.show', [$applicationId]))->with('error', 'Maximum number of offers reached.');
        }

        $type = $request->input('offer_type');
        if (!in_array($type, OfferType::values())) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Invalid offer type.');
        }

        $application = Database::fetch(
            'SELECT a.*, c.years_experience
             FROM applications a
             JOIN candidates c ON a.candidate_id = c.candidate_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            return Response::redirect(url('hr.offers.index'))->with('error', 'Application not found.');
        }

        $calculator = new OfferPackageCalculator();
        $packageLevel = $request->input('package_level') ?: 'CANDIDATE_EXPERIENCE';
        $yearsExperience = $this->yearsFromPackageLevel($packageLevel, (int)$application['years_experience']);

        $ctcInput = trim((string)($request->input('ctc') ?? ''));
        $bonusInput = trim((string)($request->input('bonus') ?? ''));
        $stockInput = trim((string)($request->input('stock_options') ?? ''));

        if ($ctcInput === '') {
            $package = $calculator->suggest($type, $yearsExperience);
        } else {
            $package = $calculator->calculate(
                $type,
                (float)$ctcInput,
                $bonusInput === '' ? null : (float)$bonusInput,
                $stockInput === '' ? null : (float)$stockInput
            );
        }

        $ctc = (float)$package['ctc'];
        $bonus = (float)$package['bonus'];
        $stock = (float)$package['stock_options'];
        
        if ($ctc < 0 || $bonus < 0 || $stock < 0) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Compensation amounts must be non-negative.');
        }

        $expiryDate = str_replace('T', ' ', (string)$request->input('expiry_date'));
        if (strtotime($expiryDate) < time()) {
            return Response::redirect(url('hr.offers.create', [$applicationId]))->with('error', 'Expiry date must be in the future.');
        }

        $replacesOfferId = count($existingOffers) > 0 ? $existingOffers[0]['offer_id'] : null;

        $offerId = OfferModel::createDraft($applicationId, $type, $ctc, $bonus, $stock, $expiryDate, $actorId, $replacesOfferId);

        $action = $replacesOfferId ? PostOfferAuditAction::OFFER_REPLACE->value : PostOfferAuditAction::OFFER_CREATE->value;

        PostOfferAuditModel::record($applicationId, $offerId, null, $actorId, $action, [
            'status' => ['new' => OfferStatus::DRAFT->value],
            'ctc' => ['new' => $ctc],
            'bonus' => ['new' => $bonus],
            'stock_options' => ['new' => $stock],
            'package_level' => ['new' => $packageLevel],
            'calculator_warnings' => ['new' => $package['warnings'] ?? []],
        ]);

        return Response::redirect(url('hr.offers.show', [$offerId]))->with('success', 'Draft offer created.');
    }

    public function show(Request $request, int $offerId): Response
    {
        $actorId = Auth::id();
        OfferModel::enforceExpiryForOffer($offerId, $actorId);

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
            $onboarding = \App\Models\OnboardingModel::findByOfferId($offerId);
        }

        $revisions = OfferModel::getRevisionChain((int)$offer['application_id']);

        return Response::view('hr/offers/show', [
            'title' => 'View Offer',
            'offer' => $offer,
            'onboarding' => $onboarding,
            'revisions' => $revisions,
        ]);
    }

    public function send(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::send()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $actorId = Auth::id();
        OfferModel::enforceExpiryForOffer($offerId, $actorId);

        $offer = OfferModel::find($offerId);
        if (!$offer || $offer['status'] !== OfferStatus::DRAFT->value) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Only draft offers can be sent.');
        }

        if (strtotime($offer['expiry_date']) < time()) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'Cannot send an offer with a past expiry date.');
        }

        OfferModel::send($offerId, $actorId);

        PostOfferAuditModel::record($offer['application_id'], $offerId, null, $actorId, PostOfferAuditAction::OFFER_SEND->value, [
            'status' => ['old' => OfferStatus::DRAFT->value, 'new' => OfferStatus::SENT->value]
        ]);

        return Response::redirect(url('hr.offers.show', [$offerId]))->with('success', 'Offer sent to candidate.');
    }

    public function generateLetter(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::create()) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $offer = Database::fetch(
            'SELECT o.*, a.job_id, j.title as job_title, d.name as department_name,
                    c.candidate_id, u.name as candidate_name
             FROM offers o
             JOIN applications a ON o.application_id = a.application_id
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN departments d ON j.department_id = d.department_id
             JOIN candidates c ON a.candidate_id = c.candidate_id
             JOIN users u ON c.candidate_id = u.user_id
             WHERE o.offer_id = ?',
            [$offerId]
        );

        if (!$offer) {
            return Response::redirect(url('hr.offers.index'))->with('error', 'Offer not found');
        }

        $actorId = Auth::id();
        $service = new \App\Services\OfferLetterTemplateService();
        $service->generateAndStore($offerId, $offer, $actorId);

        return Response::redirect(url('hr.offers.letter', [$offerId]))->with('success', 'Offer letter generated.');
    }

    public function viewLetter(Request $request, int $offerId): Response
    {
        if (!OfferPolicy::view($offerId)) {
            return Response::redirect(url('hr.dashboard'))->with('error', 'Unauthorized');
        }

        $service = new \App\Services\OfferLetterTemplateService();
        $letter = $service->getLatestLetter($offerId);

        if (!$letter) {
            return Response::redirect(url('hr.offers.show', [$offerId]))->with('error', 'No letter has been generated yet.');
        }

        return Response::view('hr/offers/letter', [
            'title' => 'Offer Letter',
            'offerId' => $offerId,
            'letter' => $letter,
        ]);
    }

    private function yearsFromPackageLevel(string $packageLevel, int $candidateYears): int
    {
        return match ($packageLevel) {
            'ENTRY' => 1,
            'MID' => 5,
            'SENIOR' => 10,
            'LEAD' => 15,
            default => $candidateYears,
        };
    }
}
