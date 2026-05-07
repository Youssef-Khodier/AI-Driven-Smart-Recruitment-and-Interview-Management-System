<?php

namespace App\Repositories;

use App\Core\Database;

final class AuditLogRepository
{
    public static function search(array $filters, int $page = 1, int $perPage = 25): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if (! empty($filters['from'])) {
            $where[] = 'occurred_at >= ?';
            $params[] = $filters['from'] . ' 00:00:00';
        }
        if (! empty($filters['to'])) {
            $where[] = 'occurred_at <= ?';
            $params[] = $filters['to'] . ' 23:59:59';
        }
        if (! empty($filters['actor'])) {
            $where[] = '(actor_name LIKE ? OR actor_email LIKE ?)';
            $params[] = '%' . $filters['actor'] . '%';
            $params[] = '%' . $filters['actor'] . '%';
        }
        if (! empty($filters['action'])) {
            $where[] = 'action LIKE ?';
            $params[] = '%' . $filters['action'] . '%';
        }
        if (! empty($filters['entity'])) {
            $where[] = 'entity_type = ?';
            $params[] = $filters['entity'];
        }

        $sql = self::baseUnionSql();
        if ($where) {
            $sql = 'SELECT * FROM (' . $sql . ') audit_union WHERE ' . implode(' AND ', $where);
        } else {
            $sql = 'SELECT * FROM (' . $sql . ') audit_union';
        }

        $countRow = Database::fetch('SELECT COUNT(*) AS count FROM (' . $sql . ') counted', $params);
        $rows = Database::fetchAll($sql . " ORDER BY occurred_at DESC LIMIT $perPage OFFSET $offset", $params);

        foreach ($rows as &$row) {
            $row['summary'] = self::formatSummary($row['raw_summary'] ?? null, $row['action'] ?? '');
        }

        return ['rows' => $rows, 'total' => (int) ($countRow['count'] ?? 0), 'page' => $page, 'per_page' => $perPage];
    }

    public static function entities(): array
    {
        return ['ACCOUNT', 'INTERVIEW', 'POST_OFFER', 'APPLICATION_STATUS', 'JOB_REQUISITION_STATUS', 'SCREENING', 'REQUISITION_GOVERNANCE', 'FEEDBACK_GOVERNANCE', 'COMPLIANCE'];
    }

    public static function formatSummary(?string $raw, string $action): string
    {
        if ($raw === null || $raw === '') {
            return $action;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded)) {
            return $raw;
        }

        $parts = [];
        foreach ($decoded as $field => $value) {
            if (is_array($value) && array_key_exists('old', $value) && array_key_exists('new', $value)) {
                $parts[] = $field . ': ' . self::stringValue($value['old']) . ' -> ' . self::stringValue($value['new']);
            } else {
                $parts[] = $field . ': ' . self::stringValue($value);
            }
        }

        return $parts ? implode('; ', $parts) : $action;
    }

    private static function stringValue(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_array($value)) {
            return json_encode($value) ?: '[]';
        }

        return (string) $value;
    }

    private static function baseUnionSql(): string
    {
        return "SELECT ar.created_at AS occurred_at, ar.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'ACCOUNT' AS entity_type, ar.target_user_id AS entity_id, ar.action, COALESCE(ar.new_values, ar.old_values) AS raw_summary
                FROM account_audit_records ar
                JOIN users actor ON actor.user_id = ar.actor_user_id
                UNION ALL
                SELECT iar.created_at AS occurred_at, iar.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'INTERVIEW' AS entity_type, iar.interview_id AS entity_id, iar.action, iar.changed_fields AS raw_summary
                FROM interview_audit_records iar
                JOIN users actor ON actor.user_id = iar.actor_user_id
                UNION ALL
                SELECT po.created_at AS occurred_at, po.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'POST_OFFER' AS entity_type, COALESCE(po.offer_id, po.onboarding_id, po.application_id) AS entity_id, po.action, po.changed_fields AS raw_summary
                FROM post_offer_audit_records po
                JOIN users actor ON actor.user_id = po.actor_user_id
                UNION ALL
                SELECT ash.created_at AS occurred_at, ash.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'APPLICATION_STATUS' AS entity_type, ash.application_id AS entity_id, CONCAT('STATUS_', ash.new_status) AS action,
                    JSON_OBJECT('status', JSON_OBJECT('old', ash.old_status, 'new', ash.new_status), 'reason', ash.reason) AS raw_summary
                FROM application_status_histories ash
                JOIN users actor ON actor.user_id = ash.actor_user_id
                UNION ALL
                SELECT jsh.created_at AS occurred_at, jsh.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'JOB_REQUISITION_STATUS' AS entity_type, jsh.job_id AS entity_id, CONCAT('STATUS_', jsh.new_status) AS action,
                    JSON_OBJECT('status', JSON_OBJECT('old', jsh.old_status, 'new', jsh.new_status), 'reason', jsh.reason) AS raw_summary
                FROM job_requisition_status_histories jsh
                JOIN users actor ON actor.user_id = jsh.actor_user_id
                UNION ALL
                SELECT sar.created_at AS occurred_at, sar.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'SCREENING' AS entity_type, COALESCE(sar.entity_id, sar.job_id) AS entity_id, sar.action, COALESCE(sar.new_values, sar.old_values) AS raw_summary
                FROM screening_audit_records sar
                JOIN users actor ON actor.user_id = sar.actor_user_id
                UNION ALL
                SELECT rga.created_at AS occurred_at, rga.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'REQUISITION_GOVERNANCE' AS entity_type, rga.job_id AS entity_id, rga.action, COALESCE(rga.new_values, rga.old_values) AS raw_summary
                FROM requisition_governance_audit rga
                JOIN users actor ON actor.user_id = rga.actor_user_id
                UNION ALL
                SELECT fga.created_at AS occurred_at, fga.actor_user_id, COALESCE(actor.name, 'System') AS actor_name, COALESCE(actor.email, '') AS actor_email,
                    'FEEDBACK_GOVERNANCE' AS entity_type, fga.entity_id, fga.action, COALESCE(fga.new_values, fga.old_values) AS raw_summary
                FROM feedback_governance_audit_records fga
                LEFT JOIN users actor ON actor.user_id = fga.actor_user_id
                UNION ALL
                SELECT cae.created_at AS occurred_at, cae.actor_user_id, actor.name AS actor_name, actor.email AS actor_email,
                    'COMPLIANCE' AS entity_type, cae.entity_id, cae.action, COALESCE(cae.new_values, cae.old_values) AS raw_summary
                FROM compliance_audit_events cae
                JOIN users actor ON actor.user_id = cae.actor_user_id";
    }
}
