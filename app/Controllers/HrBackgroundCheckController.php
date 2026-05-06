<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Session;
use App\Enums\UserRole;
use App\Services\SimulatedBackgroundCheckService;
use App\Core\Database;

final class HrBackgroundCheckController extends Controller
{
    public function index(Request $request, int $applicationId): Response
    {
        $this->requireRole(UserRole::HR_ADMIN->value);

        $application = Database::fetch(
            'SELECT a.*, j.title AS job_title, u.name AS candidate_name, a.candidate_id
             FROM applications a
             JOIN job_requisitions j ON a.job_id = j.job_id
             JOIN users u ON a.candidate_id = u.user_id
             WHERE a.application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            throw new HttpException(404, 'Application not found.');
        }

        $service = new SimulatedBackgroundCheckService();
        $checks = $service->forApplication($applicationId);
        $allPassed = $service->allChecksPassed($applicationId);

        return $this->view('hr/background-checks/index', [
            'title' => 'Background Checks — ' . $application['candidate_name'],
            'application' => $application,
            'checks' => $checks,
            'allPassed' => $allPassed,
            'checkTypes' => SimulatedBackgroundCheckService::CHECK_TYPES,
        ]);
    }

    public function request(Request $request, int $applicationId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);

        $application = Database::fetch(
            'SELECT candidate_id FROM applications WHERE application_id = ?',
            [$applicationId]
        );

        if (!$application) {
            throw new HttpException(404, 'Application not found.');
        }

        $data = $this->validate($request->body(), [
            'check_type' => ['required'],
        ]);

        $service = new SimulatedBackgroundCheckService();

        try {
            $service->request($applicationId, (int)$application['candidate_id'], $data['check_type'], (int)$user['user_id']);
            Session::flash('status', 'Background check requested.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        return $this->redirect(url('hr.background-checks.index', [$applicationId]));
    }

    public function markInProgress(Request $request, int $checkId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        $service = new SimulatedBackgroundCheckService();

        try {
            $service->markInProgress($checkId, (int)$user['user_id']);
            Session::flash('status', 'Check marked as in-progress.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        $check = $service->find($checkId);
        return $this->redirect(url('hr.background-checks.index', [$check['application_id'] ?? 0]));
    }

    public function complete(Request $request, int $checkId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        $service = new SimulatedBackgroundCheckService();
        $check = $service->find($checkId);

        if (!$check) {
            throw new HttpException(404, 'Check not found.');
        }

        $passed = $request->input('result') === 'PASSED';
        $notes = trim($request->input('notes') ?? '');

        try {
            $service->complete($checkId, $passed, $notes ?: null, (int)$user['user_id']);
            Session::flash('status', 'Background check completed: ' . ($passed ? 'PASSED' : 'FAILED'));
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        return $this->redirect(url('hr.background-checks.index', [$check['application_id']]));
    }

    public function cancel(Request $request, int $checkId): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        $service = new SimulatedBackgroundCheckService();
        $check = $service->find($checkId);

        if (!$check) {
            throw new HttpException(404, 'Check not found.');
        }

        try {
            $service->cancel($checkId, (int)$user['user_id']);
            Session::flash('status', 'Background check cancelled.');
        } catch (\RuntimeException $e) {
            Session::flash('error', $e->getMessage());
        }

        return $this->redirect(url('hr.background-checks.index', [$check['application_id']]));
    }
}
