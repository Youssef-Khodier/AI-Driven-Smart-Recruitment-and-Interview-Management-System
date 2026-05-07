<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Session;
use App\Core\Auth;
use App\Policies\ScreeningPolicy;
use App\Repositories\ScreeningConfigRepository;
use App\Repositories\ScreeningAuditRepository;
use App\Core\Database;
use App\Enums\ScreeningAuditAction;

class HrScreeningController extends Controller {
    private ScreeningConfigRepository $configRepo;
    private ScreeningAuditRepository $auditRepo;

    public function __construct() {
        $this->configRepo = new ScreeningConfigRepository();
        $this->auditRepo = new ScreeningAuditRepository();
    }

    private function getRequisition(int $id): array {
        $sql = "SELECT * FROM job_requisitions WHERE job_id = ?";
        $req = Database::fetch($sql, [$id]);
        if (!$req) {
            throw new \App\Core\HttpException("Requisition not found", 404);
        }
        return $req;
    }

    public function config($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canConfigure()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        if (!in_array($requisition['status'], ['APPROVED', 'OPEN'])) {
            Session::flash('error', 'Screening configuration is only available for APPROVED or OPEN requisitions.');
            return Response::redirect("/hr/requisitions/{$id}");
            return;
        }

        $config = $this->configRepo->findActiveByJobId($id);
        $skills = $config ? $this->configRepo->getSkills($config['config_id']) : [];
        $thresholds = $config ? $this->configRepo->getThresholds($config['config_id']) : [];
        $errors = Session::flashed('errors') ?: [];

        return Response::view('hr/screening/config', [
            'requisition' => $requisition,
            'config' => $config,
            'skills' => $skills,
            'thresholds' => $thresholds,
            'errors' => $errors
        ]);
    }

    public function storeConfig($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canConfigure()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        if (!in_array($requisition['status'], ['APPROVED', 'OPEN'])) {
            Session::flash('error', 'Screening configuration is only available for APPROVED or OPEN requisitions.');
            return Response::redirect("/hr/requisitions/{$id}");
            return;
        }

        $data = $request->all();
        $skills = $data['skills'] ?? [];
        $thresholds = $data['thresholds'] ?? [];

        $errors = [];
        if (empty($skills)) {
            $errors['skills'] = "At least one skill is required.";
        }

        $totalWeight = 0;
        foreach ($skills as $i => $skill) {
            if (empty($skill['skill_name'])) {
                $errors["skills.{$i}.name"] = "Skill name is required.";
            }
            if (!is_numeric($skill['weight']) || $skill['weight'] <= 0) {
                $errors["skills.{$i}.weight"] = "Weight must be greater than 0.";
            }
            $totalWeight += (float) ($skill['weight'] ?? 0);
        }

        if (count($skills) > 0 && abs($totalWeight - 100.0) > 0.01) {
            $errors['skills_weight'] = "Total weights must sum to exactly 100%.";
        }

        $expectedMin = 0;
        foreach ($thresholds as $i => $threshold) {
            $min = (int) ($threshold['min_score'] ?? 0);
            $max = (int) ($threshold['max_score'] ?? 0);
            $status = $threshold['target_status'] ?? '';

            if ($min !== $expectedMin) {
                $errors["thresholds.{$i}.min"] = "Thresholds must be contiguous starting from 0. Expected: $expectedMin.";
            }
            if ($max < $min || $max > 100) {
                $errors["thresholds.{$i}.max"] = "Max score must be between $min and 100.";
            }
            if (!in_array($status, ['SCREENING', 'ASSESSMENT', 'INTERVIEW', 'REJECTED'])) {
                $errors["thresholds.{$i}.status"] = "Invalid target status.";
            }
            $expectedMin = $max + 1;
        }

        if ($expectedMin !== 101 && !empty($thresholds)) {
            $errors['thresholds_max'] = "Thresholds must cover exactly up to 100.";
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            Session::flash('old_input', $data);
            return Response::redirect("/hr/requisitions/{$id}/screening");
            return;
        }

        $oldConfig = $this->configRepo->findActiveByJobId($id);
        $oldValues = null;
        if ($oldConfig) {
            $oldValues = [
                'config' => $oldConfig,
                'skills' => $this->configRepo->getSkills($oldConfig['config_id']),
                'thresholds' => $this->configRepo->getThresholds($oldConfig['config_id'])
            ];
        }

        $user = Auth::user();
        $userId = is_array($user) ? $user['user_id'] : $user->user_id;
        
        $newConfigId = $this->configRepo->saveConfig($id, $userId, $skills, $thresholds);

        $newValues = [
            'config_id' => $newConfigId,
            'skills' => $skills,
            'thresholds' => $thresholds
        ];

        $action = $oldConfig ? ScreeningAuditAction::CONFIG_UPDATED->value : ScreeningAuditAction::CONFIG_CREATED->value;
        $this->auditRepo->log($id, $userId, $action, 'CONFIG', $newConfigId, $oldValues, $newValues);

        Session::flash('success', 'Screening configuration saved successfully.');
        return Response::redirect("/hr/requisitions/{$id}/screening");
    }

    public function recalculate($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canRecalculate()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        $config = $this->configRepo->findActiveByJobId($id);
        if (!$config) {
            Session::flash('error', 'Please configure screening rules first.');
            return Response::redirect("/hr/requisitions/{$id}/screening");
            return;
        }

        $user = Auth::user();
        $userId = is_array($user) ? $user['user_id'] : $user->user_id;

        $service = new \App\Services\ScreeningScoreService();
        $result = $service->recalculateForJob($id, $userId);

        if ($result['updated_count'] === 0) {
            Session::flash('error', 'No APPLIED candidates found to recalculate.');
        } else {
            Session::flash('success', "Match scores recalculated for {$result['updated_count']} candidates.");
        }
        return Response::redirect("/hr/requisitions/{$id}/shortlist");
    }

    public function shortlist($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canViewShortlist()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        $config = $this->configRepo->findActiveByJobId($id);
        if (!$config) {
            Session::flash('error', 'Please configure screening rules first.');
            return Response::redirect("/hr/requisitions/{$id}/screening");
            return;
        }

        $service = new \App\Services\ScreeningScoreService();
        $applications = $service->getShortlist($id);

        return Response::view('hr/screening/shortlist', [
            'requisition' => $requisition,
            'config' => $config,
            'applications' => $applications
        ]);
    }
    public function triagePreview($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canTriage()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        $config = $this->configRepo->findActiveByJobId($id);
        if (!$config) {
            Session::flash('error', 'Please configure screening rules first.');
            return Response::redirect("/hr/requisitions/{$id}/screening");
            return;
        }

        $thresholds = $this->configRepo->getThresholds($config['config_id']);

        $sql = "SELECT a.application_id, a.match_score, c.years_experience, u.name, u.email 
                FROM applications a
                JOIN candidates c ON a.candidate_id = c.candidate_id
                JOIN users u ON c.candidate_id = u.user_id
                WHERE a.job_id = ? AND a.status = 'APPLIED'
                ORDER BY a.match_score DESC";
        $applications = Database::fetchAll($sql, [$id]);

        $preview = [];
        foreach ($applications as $app) {
            $score = (int)$app['match_score'];
            $targetStatus = null;
            foreach ($thresholds as $t) {
                if ($score >= (int)$t['min_score'] && $score <= (int)$t['max_score']) {
                    $targetStatus = $t['target_status'];
                    break;
                }
            }
            if ($targetStatus) {
                $app['target_status'] = $targetStatus;
                $preview[] = $app;
            }
        }

        return Response::view('hr/screening/triage-confirm', [
            'requisition' => $requisition,
            'preview' => $preview
        ]);
    }

    public function executeTriage($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canTriage()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        $user = Auth::user();
        $userId = is_array($user) ? $user['user_id'] : $user->user_id;

        $service = new \App\Services\ScreeningScoreService();
        try {
            $result = $service->executeTriage($id, $userId);
        } catch (\Exception $e) {
            Session::flash('error', $e->getMessage());
            return Response::redirect("/hr/requisitions/{$id}/screening");
            return;
        }

        return Response::view('hr/screening/triage-results', [
            'requisition' => $requisition,
            'results' => $result
        ]);
    }
    public function duplicates($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canManageDuplicates()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);
        $user = Auth::user();
        $userId = is_array($user) ? $user['user_id'] : $user->user_id;

        $service = new \App\Services\DuplicateDetectionService();
        $suggestions = $service->detectDuplicates($id);

        $this->auditRepo->log($id, $userId, ScreeningAuditAction::DUPLICATE_CHECK_RUN->value, null, null, null, ['suggestions_found' => count($suggestions)]);

        return Response::view('hr/screening/duplicates', [
            'requisition' => $requisition,
            'suggestions' => $suggestions
        ]);
    }

    public function resolveDuplicate($request, $id, $mergeId = null) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canManageDuplicates()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition($id);

        if ($request->method() === 'GET') {
            $candidateA = $request->query('candidate_a');
            $candidateB = $request->query('candidate_b');
            
            if (!$candidateA || !$candidateB) {
                return Response::redirect("/hr/requisitions/{$id}/duplicates");
                return;
            }

            $sql = "SELECT c.*, u.name, u.email 
                    FROM candidates c
                    JOIN users u ON c.candidate_id = u.user_id
                    WHERE c.candidate_id IN (?, ?)";
            $candidates = Database::fetchAll($sql, [$candidateA, $candidateB]);
            
            if (count($candidates) !== 2) {
                return Response::redirect("/hr/requisitions/{$id}/duplicates");
                return;
            }

            $cA = $candidates[0]['candidate_id'] == $candidateA ? $candidates[0] : $candidates[1];
            $cB = $candidates[0]['candidate_id'] == $candidateB ? $candidates[0] : $candidates[1];

            $suggestion = [
                'candidate_a' => $cA,
                'candidate_b' => $cB,
                'confidence' => $request->query('confidence', 'LOW')
            ];

            return Response::view('hr/screening/duplicate-resolve', [
                'requisition' => $requisition,
                'suggestion' => $suggestion,
                'errors' => Session::flashed('errors') ?: []
            ]);
            return;
        }

        $decisionType = $request->input('decision_type');
        $notes = $request->input('notes');
        $primaryId = $request->input('primary_candidate_id');
        $candidateA = $request->input('candidate_a');
        $candidateB = $request->input('candidate_b');
        $confidence = $request->input('confidence', 'LOW');

        $errors = [];
        if (!in_array($decisionType, ['MERGE', 'IGNORE', 'DEFER'])) {
            $errors['decision_type'] = "Invalid decision type.";
        }
        if (empty(trim($notes))) {
            $errors['notes'] = "Reason is required.";
        }

        if ($decisionType === 'MERGE') {
            if (!$primaryId) {
                $errors['primary_candidate_id'] = "Primary candidate must be selected for merge.";
            }
            $duplicateId = ($primaryId == $candidateA) ? $candidateB : $candidateA;
        } else {
            $primaryId = $candidateA;
            $duplicateId = $candidateB;
        }

        if (!empty($errors)) {
            Session::flash('errors', $errors);
            $url = "/hr/requisitions/{$id}/duplicates/resolve?candidate_a={$candidateA}&candidate_b={$candidateB}&confidence={$confidence}";
            return Response::redirect($url);
            return;
        }

        $user = Auth::user();
        $userId = is_array($user) ? $user['user_id'] : $user->user_id;

        $repo = new \App\Repositories\DuplicateRepository();
        $mergeIdRecord = $repo->recordDecision($primaryId, $duplicateId, $userId, $decisionType, $confidence, $id, null, $notes);

        $this->auditRepo->log($id, $userId, ScreeningAuditAction::DUPLICATE_DECISION->value, 'MERGE', $mergeIdRecord, null, [
            'primary_id' => $primaryId,
            'duplicate_id' => $duplicateId,
            'decision_type' => $decisionType,
            'confidence' => $confidence
        ]);

        Session::flash('success', 'Duplicate decision recorded successfully.');
        return Response::redirect("/hr/requisitions/{$id}/duplicates");
    }
    public function audit($request, $id) {
        $this->requireRole('HR_ADMIN');
        if (!ScreeningPolicy::canViewAudit()) {
            throw new \App\Core\HttpException("Forbidden", 403);
        }

        $requisition = $this->getRequisition((int) $id);
        $filters = [
            'job_id' => (int) $id,
            'action_type' => $request->input('action_type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $page = max(1, (int) $request->input('page', 1));
        $perPage = 25;
        $result = $this->auditRepo->search($filters, $page, $perPage);
        $totalRecords = (int) ($result['total'] ?? 0);

        return $this->view('hr/screening/audit', [
            'requisition' => $requisition,
            'records' => $result['data'] ?? [],
            'filters' => $filters,
            'pagination' => [
                'current' => $page,
                'total' => max(1, (int) ceil($totalRecords / $perPage)),
                'total_records' => $totalRecords,
            ],
            'title' => 'Screening Audit Log',
        ]);
    }
}

