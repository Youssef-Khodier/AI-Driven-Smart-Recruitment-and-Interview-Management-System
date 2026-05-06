<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Session;
use App\Enums\UserRole;
use App\Repositories\ReferralRepository;
use App\Core\Database;

final class HrReferralController extends Controller
{
    public function index(Request $request): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        $referrals = ReferralRepository::allWithDetails();
        $summary = ReferralRepository::summary();

        return $this->view('hr/referrals/index', [
            'title' => 'Referral Tracking',
            'referrals' => $referrals,
            'summary' => $summary,
        ]);
    }

    public function create(Request $request, int $applicationId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        $application = Database::fetch(
            'SELECT a.*, j.title AS job_title, u.name AS candidate_name
             FROM applications a
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN users u ON a.candidate_id = u.user_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            throw new HttpException(404, 'Application not found.');
        }

        $existingReferral = ReferralRepository::findByApplication($applicationId);

        $internalUsers = Database::fetchAll(
            'SELECT user_id, name, email FROM users WHERE role != ? AND status = ? ORDER BY name',
            [UserRole::CANDIDATE->value, 'ACTIVE']
        );

        return $this->view('hr/referrals/form', [
            'title' => 'Add Referral',
            'application' => $application,
            'existingReferral' => $existingReferral,
            'internalUsers' => $internalUsers,
        ]);
    }

    public function store(Request $request, int $applicationId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        $existing = ReferralRepository::findByApplication($applicationId);
        if ($existing) {
            return $this->redirect(url('hr.referrals.index'))->with('error', 'A referral already exists for this application.');
        }

        $application = Database::fetch(
            'SELECT candidate_id FROM applications WHERE application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            throw new HttpException(404, 'Application not found.');
        }

        $data = $this->validate($request->body(), [
            'referral_source' => ['required'],
        ]);

        $referrerUserId = $request->input('referrer_user_id') ? (int)$request->input('referrer_user_id') : null;
        $rewardAmount = $request->input('reward_amount') ? (float)$request->input('reward_amount') : null;

        $referralId = ReferralRepository::create([
            'application_id' => $applicationId,
            'candidate_id' => (int)$application['candidate_id'],
            'referrer_user_id' => $referrerUserId,
            'referrer_name' => trim($request->input('referrer_name') ?? ''),
            'referrer_email' => trim($request->input('referrer_email') ?? ''),
            'referral_source' => $data['referral_source'],
            'notes' => trim($request->input('notes') ?? ''),
        ]);

        // Auto-create pending reward if amount provided
        if ($rewardAmount !== null && $rewardAmount > 0) {
            ReferralRepository::createReward($referralId, $rewardAmount);
        }

        Session::flash('status', 'Referral recorded successfully.');
        return $this->redirect(url('hr.referrals.index'));
    }

    public function approveReward(Request $request, int $referralId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);

        $reward = ReferralRepository::getReward($referralId);
        if (!$reward || $reward['reward_status'] !== 'PENDING') {
            return $this->redirect(url('hr.referrals.index'))->with('error', 'Reward not found or already processed.');
        }

        ReferralRepository::approveReward($reward['reward_id'], (int)$user['user_id']);

        Session::flash('status', 'Reward approved.');
        return $this->redirect(url('hr.referrals.index'));
    }

    public function rejectReward(Request $request, int $referralId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);

        $reward = ReferralRepository::getReward($referralId);
        if (!$reward || $reward['reward_status'] !== 'PENDING') {
            return $this->redirect(url('hr.referrals.index'))->with('error', 'Reward not found or already processed.');
        }

        $notes = trim($request->input('notes') ?? '');
        ReferralRepository::rejectReward($reward['reward_id'], (int)$user['user_id'], $notes);

        Session::flash('status', 'Reward rejected.');
        return $this->redirect(url('hr.referrals.index'));
    }

    public function markPaid(Request $request, int $referralId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        $reward = ReferralRepository::getReward($referralId);
        if (!$reward || $reward['reward_status'] !== 'APPROVED') {
            return $this->redirect(url('hr.referrals.index'))->with('error', 'Reward must be approved before marking as paid.');
        }

        ReferralRepository::markPaid($reward['reward_id']);

        Session::flash('status', 'Reward marked as paid.');
        return $this->redirect(url('hr.referrals.index'));
    }
}
