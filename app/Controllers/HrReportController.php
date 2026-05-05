<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Enums\UserRole;
use App\Policies\ReportPolicy;
use App\Repositories\ReportRepository;

final class HrReportController extends Controller
{
    public function pipeline(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        if (! (new ReportPolicy())->viewPipeline($user)) {
            throw new HttpException(403, 'You are not authorized to view reports.');
        }

        return $this->view('hr/reports/pipeline', ReportRepository::pipelineByOpenRequisition() + [
            'title' => 'Pipeline Report',
        ]);
    }

    public function timeToHire(Request $request): Response
    {
        $user = $this->requireRole(UserRole::HR_ADMIN->value);
        if (! (new ReportPolicy())->viewTimeToHire($user)) {
            throw new HttpException(403, 'You are not authorized to view reports.');
        }

        return $this->view('hr/reports/time-to-hire', [
            'title' => 'Time to Hire',
            'requisitions' => ReportRepository::timeToHireByRequisition(),
            'departments' => ReportRepository::timeToHireByDepartment(),
        ]);
    }
}
