<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Enums\UserRole;

final class DashboardController extends Controller
{
    public function redirectToDashboard(Request $request): Response
    {
        $user = $this->requireAuth();

        return $this->redirect(match ($user['role']) {
            UserRole::HR_ADMIN->value => url('hr.dashboard'),
            UserRole::INTERVIEWER->value => url('interviewer.dashboard'),
            default => url('candidate.dashboard'),
        });
    }

    public function candidate(Request $request): Response
    {
        $user = $this->requireRole(UserRole::CANDIDATE->value);

        return $this->view('candidate/dashboard', ['title' => 'Candidate Dashboard', 'user' => $user]);
    }

    public function hr(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);

        $pendingApprovalsCount = 0;
        if ($user['is_department_head'] ?? false) {
            $repo = new \App\Models\GovernanceModel();
            $pendingApprovalsCount = count($repo->getPendingApprovals($user['department_id']));
        }

        return $this->view('hr/dashboard', ['title' => 'HR Dashboard', 'user' => $user, 'pendingApprovalsCount' => $pendingApprovalsCount]);
    }

    public function interviewer(Request $request): Response
    {
        $user = $this->requireRole(UserRole::INTERVIEWER->value);

        return $this->view('interviewer/dashboard', ['title' => 'Interviewer Dashboard', 'user' => $user]);
    }
}
