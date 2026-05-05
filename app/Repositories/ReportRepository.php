<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\ApplicationStatus;
use App\Enums\JobRequisitionStatus;

final class ReportRepository
{
    public static function pipelineByOpenRequisition(): array
    {
        $statuses = ApplicationStatus::values();
        $rows = Database::fetchAll(
            'SELECT j.job_id, j.title, d.name AS department_name, a.status, COUNT(a.application_id) AS count
             FROM job_requisitions j
             JOIN departments d ON d.department_id = j.department_id
             LEFT JOIN applications a ON a.job_id = j.job_id
             WHERE j.status = ?
             GROUP BY j.job_id, j.title, d.name, a.status
             ORDER BY j.title',
            [JobRequisitionStatus::OPEN->value]
        );

        $reports = [];
        $totals = array_fill_keys($statuses, 0);
        foreach ($rows as $row) {
            $jobId = (int) $row['job_id'];
            if (! isset($reports[$jobId])) {
                $reports[$jobId] = [
                    'job_id' => $jobId,
                    'title' => $row['title'],
                    'department_name' => $row['department_name'],
                    'counts' => array_fill_keys($statuses, 0),
                    'total' => 0,
                ];
            }
            if ($row['status'] !== null && in_array($row['status'], $statuses, true)) {
                $count = (int) $row['count'];
                $reports[$jobId]['counts'][$row['status']] = $count;
                $reports[$jobId]['total'] += $count;
                $totals[$row['status']] += $count;
            }
        }

        return ['statuses' => $statuses, 'rows' => array_values($reports), 'totals' => $totals, 'grand_total' => array_sum($totals)];
    }

    public static function timeToHireByRequisition(): array
    {
        return Database::fetchAll(
            "SELECT j.job_id, j.title, d.name AS department_name, COUNT(hired.application_id) AS hired_count,
                    ROUND(AVG(TIMESTAMPDIFF(DAY, a.applied_at, hired.hired_at)), 1) AS average_days
             FROM job_requisitions j
             JOIN departments d ON d.department_id = j.department_id
             LEFT JOIN applications a ON a.job_id = j.job_id
             LEFT JOIN (
                SELECT application_id, MIN(created_at) AS hired_at
                FROM application_status_histories
                WHERE new_status = 'HIRED'
                GROUP BY application_id
             ) hired ON hired.application_id = a.application_id
             GROUP BY j.job_id, j.title, d.name
             ORDER BY j.title"
        );
    }

    public static function timeToHireByDepartment(): array
    {
        return Database::fetchAll(
            "SELECT d.department_id, d.name AS department_name, COUNT(hired.application_id) AS hired_count,
                    ROUND(AVG(TIMESTAMPDIFF(DAY, a.applied_at, hired.hired_at)), 1) AS average_days
             FROM departments d
             LEFT JOIN job_requisitions j ON j.department_id = d.department_id
             LEFT JOIN applications a ON a.job_id = j.job_id
             LEFT JOIN (
                SELECT application_id, MIN(created_at) AS hired_at
                FROM application_status_histories
                WHERE new_status = 'HIRED'
                GROUP BY application_id
             ) hired ON hired.application_id = a.application_id
             GROUP BY d.department_id, d.name
             ORDER BY d.name"
        );
    }
}
