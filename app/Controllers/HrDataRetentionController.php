<?php

namespace App\Controllers;

use App\Core\Config;
use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\ValidationException;
use App\Enums\UserRole;
use App\Policies\DataRetentionPolicy;
use App\Models\DataRetentionModel;

final class HrDataRetentionController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        if (! (new DataRetentionPolicy())->performAction($user)) {
            throw new HttpException(403, 'You are not authorized to manage data retention.');
        }

        $days = $this->retentionDays();
        return $this->view('hr/data-retention/index', [
            'title' => 'Candidate Data Retention',
            'candidates' => DataRetentionModel::eligibleCandidates($days),
            'retentionDays' => $days,
        ]);
    }

    public function anonymize(Request $request, string $candidate): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $this->requireConfirmation($request, 'ANONYMIZE');

        if (! DataRetentionModel::anonymize((int) $candidate, (int) $actor['user_id'], $this->retentionDays())) {
            throw new ValidationException(['candidate' => ['Candidate is not eligible for anonymization.']]);
        }

        Session::flash('status', 'Candidate data anonymized.');
        return $this->redirect(url('hr.data-retention.index'));
    }

    public function delete(Request $request, string $candidate): Response
    {
        $actor = $this->requireRole(UserRole::HR_ADMIN->value);
        $this->requireConfirmation($request, 'DELETE');

        if (! DataRetentionModel::delete((int) $candidate, (int) $actor['user_id'], $this->retentionDays())) {
            throw new ValidationException(['candidate' => ['Candidate is not eligible for deletion.']]);
        }

        Session::flash('status', 'Candidate data deleted.');
        return $this->redirect(url('hr.data-retention.index'));
    }

    private function requireConfirmation(Request $request, string $expected): void
    {
        if (strtoupper(trim((string) $request->input('confirm'))) !== $expected) {
            throw new ValidationException(['confirm' => ["Type $expected to confirm this irreversible action."]]);
        }
    }

    private function retentionDays(): int
    {
        return max(1, (int) Config::get('CANDIDATE_RETENTION_DAYS', 365));
    }
}
