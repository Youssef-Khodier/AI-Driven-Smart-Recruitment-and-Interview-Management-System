<?php

namespace App\Repositories;

use App\Core\Database;

class ScreeningAuditRepository {
    public function log(int $jobId, int $actorId, string $action, ?string $entityType, ?int $entityId, ?array $oldValues, ?array $newValues): void {
        $sql = "INSERT INTO screening_audit_records (job_id, actor_user_id, action, entity_type, entity_id, old_values, new_values)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        Database::query($sql, [
            $jobId,
            $actorId,
            $action,
            $entityType,
            $entityId,
            $oldValues ? json_encode($oldValues) : null,
            $newValues ? json_encode($newValues) : null
        ]);
    }

    public function search(array $filters, int $page, int $perPage): array {
        $where = ['1=1'];
        $params = [];

        if (!empty($filters['job_id'])) {
            $where[] = "sar.job_id = ?";
            $params[] = $filters['job_id'];
        }
        if (!empty($filters['action_type'])) {
            $where[] = "sar.action = ?";
            $params[] = $filters['action_type'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "sar.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "sar.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        $whereClause = implode(' AND ', $where);

        $countSql = "SELECT COUNT(*) as total FROM screening_audit_records sar WHERE $whereClause";
        $total = Database::fetch($countSql, $params)['total'] ?? 0;

        $offset = ($page - 1) * $perPage;
        $sql = "SELECT sar.*, u.name as actor_name 
                FROM screening_audit_records sar
                LEFT JOIN users u ON sar.actor_user_id = u.user_id
                WHERE $whereClause 
                ORDER BY sar.created_at DESC 
                LIMIT ? OFFSET ?";
        
        $records = Database::fetchAll($sql, array_merge($params, [$perPage, $offset]));

        return ['data' => $records, 'total' => $total];
    }
}
