<?php

namespace App\Repositories;

use App\Core\Database;

class GovernanceRepository
{
    public function getPendingApprovals(int $departmentId): array
    {
        return Database::query("
            SELECT jr.*, u.name as creator_name 
            FROM job_requisitions jr 
            JOIN users u ON jr.created_by = u.user_id 
            WHERE jr.status = 'PENDING' AND jr.department_id = ?
            ORDER BY jr.created_at DESC
        ", [$departmentId])->fetchAll();
    }

    public function recordApprovalStep(int $jobId, int $approverId, string $decision, ?string $comments = null): void
    {
        $sql = "INSERT INTO requisition_approval_steps (job_id, approver_id, decision, comments) VALUES (?, ?, ?, ?)";
        Database::query($sql, [$jobId, $approverId, $decision, $comments]);
    }

    public function getApprovalHistory(int $jobId): array
    {
        return Database::query("
            SELECT ras.*, u.name as approver_name 
            FROM requisition_approval_steps ras 
            JOIN users u ON ras.approver_id = u.user_id 
            WHERE ras.job_id = ? 
            ORDER BY ras.created_at ASC
        ", [$jobId])->fetchAll();
    }

    public function createTemplateVersion(int $jobId, string $descriptionBody, string $requirementsBody, int $createdBy): int
    {
        $versionNumber = $this->getLatestVersionNumber($jobId) + 1;
        $sql = "INSERT INTO requisition_template_versions (job_id, version_number, description_body, requirements_body, created_by) VALUES (?, ?, ?, ?, ?)";
        Database::query($sql, [$jobId, $versionNumber, $descriptionBody, $requirementsBody, $createdBy]);
        $versionId = Database::pdo()->lastInsertId();

        $this->recordGovernanceAudit($jobId, $createdBy, 'TEMPLATE_VERSION_CREATED', null, ['version_number' => $versionNumber], 'Version ' . $versionNumber . ' created');

        return $versionId;
    }

    public function getVersionHistory(int $jobId): array
    {
        return Database::query("
            SELECT rtv.*, u.name as creator_name 
            FROM requisition_template_versions rtv 
            JOIN users u ON rtv.created_by = u.user_id 
            WHERE rtv.job_id = ? 
            ORDER BY rtv.version_number DESC
        ", [$jobId])->fetchAll();
    }

    public function getVersion(int $jobId, int $versionId): ?array
    {
        return Database::query("
            SELECT rtv.*, u.name as creator_name 
            FROM requisition_template_versions rtv 
            JOIN users u ON rtv.created_by = u.user_id 
            WHERE rtv.job_id = ? AND rtv.version_id = ?
        ", [$jobId, $versionId])->fetch() ?: null;
    }

    public function getLatestVersionNumber(int $jobId): int
    {
        $result = Database::query("SELECT MAX(version_number) as max_version FROM requisition_template_versions WHERE job_id = ?", [$jobId])->fetch();
        return (int)($result['max_version'] ?? 0);
    }

    public function getActivePlatforms(): array
    {
        return Database::query("SELECT * FROM job_board_platforms WHERE is_active = TRUE ORDER BY name")->fetchAll();
    }

    public function createSyncRecord(int $jobId, int $platformId, int $createdBy, string $status = 'QUEUED'): int
    {
        $req = Database::query("
            SELECT jr.title, jr.description, jr.requirements, d.name as department_name 
            FROM job_requisitions jr 
            JOIN departments d ON jr.department_id = d.department_id 
            WHERE jr.job_id = ?
        ", [$jobId])->fetch();

        $payloadSummary = json_encode([
            'title' => $req['title'] ?? '',
            'department' => $req['department_name'] ?? '',
            'description_excerpt' => substr($req['description'] ?? '', 0, 200),
            'requirements' => $req['requirements'] ?? ''
        ]);

        $sql = "INSERT INTO job_board_sync_records (job_id, platform_id, payload_summary, status, created_by) VALUES (?, ?, ?, ?, ?)";
        Database::query($sql, [$jobId, $platformId, $payloadSummary, $status, $createdBy]);
        $syncId = Database::pdo()->lastInsertId();

        if ($status === 'QUEUED') {
            Database::query("UPDATE job_board_sync_records SET status = 'PUBLISHED', completed_at = NOW() WHERE sync_id = ?", [$syncId]);
            $this->recordGovernanceAudit($jobId, $createdBy, 'SYNC_PUBLISHED', null, ['platform_id' => $platformId, 'sync_id' => $syncId], 'Published to job board');
        } elseif ($status === 'UNPUBLISHED') {
            Database::query("UPDATE job_board_sync_records SET completed_at = NOW() WHERE sync_id = ?", [$syncId]);
        }

        return $syncId;
    }

    public function getSyncHistory(int $jobId): array
    {
        return Database::query("
            SELECT s.*, p.name as platform_name, u.name as creator_name 
            FROM job_board_sync_records s 
            JOIN job_board_platforms p ON s.platform_id = p.platform_id 
            JOIN users u ON s.created_by = u.user_id 
            WHERE s.job_id = ? 
            ORDER BY s.queued_at DESC
        ", [$jobId])->fetchAll();
    }

    public function hasPublishedSync(int $jobId, int $platformId): bool
    {
        $result = Database::query("SELECT EXISTS(SELECT 1 FROM job_board_sync_records WHERE job_id = ? AND platform_id = ? AND status = 'PUBLISHED') as has_sync", [$jobId, $platformId])->fetch();
        return (bool)($result['has_sync'] ?? false);
    }

    public function getPublishedPlatforms(int $jobId): array
    {
        $records = Database::query("SELECT platform_id FROM job_board_sync_records WHERE job_id = ? AND status = 'PUBLISHED'", [$jobId])->fetchAll();
        return array_column($records, 'platform_id');
    }

    public function recordGovernanceAudit(int $jobId, int $actorUserId, string $action, ?array $oldValues = null, ?array $newValues = null, ?string $comments = null): void
    {
        $sql = "INSERT INTO requisition_governance_audit (job_id, actor_user_id, action, old_values, new_values, comments) VALUES (?, ?, ?, ?, ?, ?)";
        Database::query($sql, [
            $jobId,
            $actorUserId,
            $action,
            $oldValues !== null ? json_encode($oldValues) : null,
            $newValues !== null ? json_encode($newValues) : null,
            $comments
        ]);
    }

    public function getGovernanceAuditLog(int $jobId, array $filters): array
    {
        $params = [$jobId];
        $whereSql = "rga.job_id = ?";

        if (!empty($filters['action'])) {
            $whereSql .= " AND rga.action = ?";
            $params[] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $whereSql .= " AND rga.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $whereSql .= " AND rga.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['actor'])) {
            $whereSql .= " AND u.name LIKE ?";
            $params[] = '%' . $filters['actor'] . '%';
        }

        $countSql = "SELECT COUNT(*) as total FROM requisition_governance_audit rga JOIN users u ON rga.actor_user_id = u.user_id WHERE $whereSql";
        $total = Database::query($countSql, $params)->fetch()['total'];

        $page = isset($filters['page']) ? max(1, (int)$filters['page']) : 1;
        $perPage = 25;
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT rga.*, u.name as actor_name 
                FROM requisition_governance_audit rga 
                JOIN users u ON rga.actor_user_id = u.user_id 
                WHERE $whereSql 
                ORDER BY rga.created_at DESC 
                LIMIT $perPage OFFSET $offset";
        
        $rows = Database::query($sql, $params)->fetchAll();

        return ['rows' => $rows, 'total' => $total];
    }

    public function getDepartmentHeads(): array
    {
        return Database::query("
            SELECT u.*, d.name as department_name 
            FROM users u 
            JOIN departments d ON u.department_id = d.department_id 
            WHERE u.role = 'HR_ADMIN' AND u.is_department_head = 1
        ")->fetchAll();
    }

    public function setDepartmentHead(int $userId, bool $isHead): void
    {
        if ($isHead) {
            $user = Database::query("SELECT department_id FROM users WHERE user_id = ?", [$userId])->fetch();
            if ($user && $user['department_id']) {
                $existingHead = Database::query("SELECT user_id FROM users WHERE department_id = ? AND is_department_head = 1 AND user_id != ?", [$user['department_id'], $userId])->fetch();
                if ($existingHead) {
                    throw new \Exception("Department already has a head assigned.");
                }
            }
        }
        Database::query("UPDATE users SET is_department_head = ? WHERE user_id = ?", [(int)$isHead, $userId]);
    }
}
