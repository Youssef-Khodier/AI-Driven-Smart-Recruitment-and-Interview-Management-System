<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Response;
use App\Core\Session;
use App\Core\View;
use App\Repositories\GovernanceRepository;
use App\Policies\GovernancePolicy;
use App\Enums\JobRequisitionStatus;
use App\Enums\GovernanceAuditAction;
use App\Enums\ApprovalDecision;
use App\Services\TemplateVersionDiffService;

class HrGovernanceController extends Controller
{
    private GovernanceRepository $repo;
    private GovernancePolicy $policy;

    public function __construct()
    {
        $this->repo = new GovernanceRepository();
        $this->policy = new GovernancePolicy();
    }

    public function approvalQueue()
    {
        $user = Auth::user();
        if (!$this->policy->viewApprovalQueue($user)) {
            throw new HttpException(403, 'Forbidden');
        }

        $requisitions = $this->repo->getPendingApprovals($user['department_id']);
        return $this->view('hr/governance/approval-queue', ['requisitions' => $requisitions]);
    }

    public function approveForm($request, $id)
    {
        $user = Auth::user();
        $requisition = Database::query("SELECT r.*, d.name AS department_name, u.name AS creator_name FROM job_requisitions r JOIN departments d ON d.department_id = r.department_id JOIN users u ON u.user_id = r.created_by WHERE r.job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        if (!$this->policy->approveRequisition($user, $requisition)) {
            throw new HttpException(403, 'Forbidden');
        }

        return $this->view('hr/governance/approve-form', ['requisition' => $requisition]);
    }

    public function approveRequisition($request, $id)
    {
        $user = Auth::user();
        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        if (!$this->policy->approveRequisition($user, $requisition)) {
            throw new HttpException(403, 'Forbidden');
        }

        $comments = $request->input('comments');
        $this->repo->recordApprovalStep($id, $user['user_id'], ApprovalDecision::APPROVED->value, $comments);
        
        Database::query("UPDATE job_requisitions SET status = ?, approved_by = ?, approved_at = NOW() WHERE job_id = ?", [JobRequisitionStatus::APPROVED->value, $user['user_id'], $id]);
        
        $this->repo->recordGovernanceAudit($id, $user['user_id'], GovernanceAuditAction::REQUISITION_APPROVED->value, null, ['status' => JobRequisitionStatus::APPROVED->value], $comments);

        Session::flash('status', 'Requisition approved successfully.');
        return Response::redirect(url('hr.approvals.index'));
    }

    public function rejectRequisition($request, $id)
    {
        $user = Auth::user();
        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        if (!$this->policy->approveRequisition($user, $requisition)) {
            throw new HttpException(403, 'Forbidden');
        }

        $comments = $request->input('comments');
        $this->repo->recordApprovalStep($id, $user['user_id'], ApprovalDecision::REJECTED->value, $comments);
        
        Database::query("UPDATE job_requisitions SET status = ? WHERE job_id = ?", [JobRequisitionStatus::REJECTED->value, $id]);
        
        $this->repo->recordGovernanceAudit($id, $user['user_id'], GovernanceAuditAction::REQUISITION_REJECTED->value, null, ['status' => JobRequisitionStatus::REJECTED->value], $comments);

        Session::flash('status', 'Requisition rejected.');
        return Response::redirect(url('hr.approvals.index'));
    }

    public function versionHistory($request, $id)
    {
        $user = Auth::user();
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $versions = $this->repo->getVersionHistory($id);

        return $this->view('hr/governance/version-history', [
            'requisition' => $requisition,
            'versions' => $versions
        ]);
    }

    public function showVersion($request, $id, $versionId)
    {
        $user = Auth::user();
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $version = $this->repo->getVersion($id, $versionId);

        if (!$version) {
            throw new HttpException(404, 'Version Not Found');
        }

        return $this->view('hr/governance/version-show', [
            'requisition' => $requisition,
            'version' => $version
        ]);
    }

    public function compareVersions($request, $id)
    {
        $user = Auth::user();
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $v1Id = $_GET['v1'] ?? null;
        $v2Id = $_GET['v2'] ?? null;

        if (!$v1Id || !$v2Id) {
            throw new HttpException(400, 'Two versions are required for comparison');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $v1 = $this->repo->getVersion($id, $v1Id);
        $v2 = $this->repo->getVersion($id, $v2Id);

        if (!$v1 || !$v2) {
            throw new HttpException(404, 'One or both versions not found');
        }

        // Ensure v1 is the older version
        if ($v1['version_number'] > $v2['version_number']) {
            $temp = $v1;
            $v1 = $v2;
            $v2 = $temp;
        }

        $diffDescription = TemplateVersionDiffService::diff($v1['description_body'], $v2['description_body']);
        $diffRequirements = TemplateVersionDiffService::diff($v1['requirements_body'], $v2['requirements_body']);

        return $this->view('hr/governance/version-compare', [
            'requisition' => $requisition,
            'v1' => $v1,
            'v2' => $v2,
            'diffDescription' => $diffDescription,
            'diffRequirements' => $diffRequirements
        ]);
    }

    public function publishForm($request, $id)
    {
        $user = Auth::user();
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        if (!$this->policy->publishRequisition($user, $requisition)) {
            throw new HttpException(403, 'Forbidden');
        }

        $activePlatforms = $this->repo->getActivePlatforms();
        $publishedPlatforms = $this->repo->getPublishedPlatforms($id);

        return $this->view('hr/governance/publish-form', [
            'requisition' => $requisition,
            'activePlatforms' => $activePlatforms,
            'publishedPlatforms' => $publishedPlatforms
        ]);
    }

    public function publishRequisition($request, $id)
    {
        $user = Auth::user();
        
        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        if (!$this->policy->publishRequisition($user, $requisition)) {
            throw new HttpException(403, 'Forbidden');
        }

        $selectedPlatforms = $request->input('platforms', []);
        if (!is_array($selectedPlatforms) || empty($selectedPlatforms)) {
            Session::flash('error', 'Please select at least one platform.');
            return Response::redirect(url('hr.requisitions.publish.form', [$id]));
        }

        $publishedCount = 0;
        foreach ($selectedPlatforms as $platformId) {
            $platformId = (int)$platformId;
            if (!$this->repo->hasPublishedSync($id, $platformId)) {
                $this->repo->createSyncRecord($id, $platformId, $user['user_id'], 'QUEUED');
                $publishedCount++;
            }
        }

        Session::flash('status', "Successfully published to $publishedCount platform(s).");
        return Response::redirect(url('hr.requisitions.show', [$id]));
    }

    public function unpublishRequisition($request, $id)
    {
        $user = Auth::user();
        
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $publishedPlatforms = $this->repo->getPublishedPlatforms($id);
        foreach ($publishedPlatforms as $platformId) {
            $this->repo->createSyncRecord($id, $platformId, $user['user_id'], 'UNPUBLISHED');
        }

        $this->repo->recordGovernanceAudit($id, $user['user_id'], GovernanceAuditAction::SYNC_UNPUBLISHED->value, null, null, 'Unpublished from all platforms');

        Session::flash('status', 'Successfully unpublished from job boards.');
        return Response::redirect(url('hr.requisitions.show', [$id]));
    }

    public function syncHistory($request, $id)
    {
        $user = Auth::user();
        if ($user['role'] !== 'HR_ADMIN') {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $syncRecords = $this->repo->getSyncHistory($id);

        return $this->view('hr/governance/sync-history', [
            'requisition' => $requisition,
            'syncRecords' => $syncRecords
        ]);
    }

    public function governanceAudit($request, $id)
    {
        $user = Auth::user();
        if (!$this->policy->viewGovernance($user)) {
            throw new HttpException(403, 'Forbidden');
        }

        $requisition = Database::query("SELECT * FROM job_requisitions WHERE job_id = ?", [$id])->fetch();

        if (!$requisition) {
            throw new HttpException(404, 'Not Found');
        }

        $filters = [
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null,
            'actor' => $_GET['actor'] ?? null,
            'page' => $_GET['page'] ?? 1,
        ];

        $logs = $this->repo->getGovernanceAuditLog($id, $filters);

        return $this->view('hr/governance/governance-audit', [
            'requisition' => $requisition,
            'logs' => $logs
        ]);
    }

}
