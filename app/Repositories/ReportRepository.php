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

    /**
     * Average days spent at each pipeline stage across all applications.
     */
    public static function averageStageDurations(): array
    {
        return Database::fetchAll(
            "SELECT
                h1.new_status AS stage,
                COUNT(*) AS transition_count,
                ROUND(AVG(TIMESTAMPDIFF(HOUR, h1.created_at, COALESCE(h2.created_at, NOW())) / 24.0), 1) AS avg_days,
                ROUND(MAX(TIMESTAMPDIFF(HOUR, h1.created_at, COALESCE(h2.created_at, NOW())) / 24.0), 1) AS max_days
             FROM application_status_histories h1
             LEFT JOIN (
                SELECT application_id, old_status, MIN(created_at) AS created_at
                FROM application_status_histories
                GROUP BY application_id, old_status
             ) h2 ON h2.application_id = h1.application_id AND h2.old_status = h1.new_status
             WHERE h1.new_status NOT IN ('HIRED', 'REJECTED', 'WITHDRAWN')
             GROUP BY h1.new_status
             ORDER BY avg_days DESC"
        );
    }

    /**
     * Conversion rates between consecutive pipeline stages.
     */
    public static function stageConversionRates(): array
    {
        $stages = ['APPLIED', 'SCREENING', 'ASSESSMENT', 'INTERVIEW', 'EVALUATION', 'OFFER', 'HIRED'];
        $rates = [];

        for ($i = 0; $i < count($stages) - 1; $i++) {
            $from = $stages[$i];
            $to = $stages[$i + 1];

            $fromCount = Database::fetch(
                "SELECT COUNT(DISTINCT application_id) AS c FROM application_status_histories WHERE new_status = ?",
                [$from]
            );
            $toCount = Database::fetch(
                "SELECT COUNT(DISTINCT application_id) AS c FROM application_status_histories WHERE new_status = ?",
                [$to]
            );

            $fromC = (int)($fromCount['c'] ?? 0);
            $toC = (int)($toCount['c'] ?? 0);

            $rates[] = [
                'from_stage' => $from,
                'to_stage' => $to,
                'from_count' => $fromC,
                'to_count' => $toC,
                'conversion_rate' => $fromC > 0 ? round(($toC / $fromC) * 100, 1) : 0,
            ];
        }

        return $rates;
    }

    /**
     * Identify bottleneck stages where average duration exceeds a threshold.
     */
    public static function identifyBottlenecks(float $thresholdDays = 7.0): array
    {
        $durations = self::averageStageDurations();
        $bottlenecks = [];

        foreach ($durations as $stage) {
            if ((float)$stage['avg_days'] >= $thresholdDays) {
                $severity = (float)$stage['avg_days'] >= ($thresholdDays * 2) ? 'HIGH' : 'MEDIUM';
                $bottlenecks[] = array_merge($stage, [
                    'severity' => $severity,
                    'threshold_days' => $thresholdDays,
                    'recommendation' => self::bottleneckRecommendation($stage['stage'], (float)$stage['avg_days']),
                ]);
            }
        }

        return $bottlenecks;
    }

    private static function bottleneckRecommendation(string $stage, float $avgDays): string
    {
        return match ($stage) {
            'SCREENING' => "Average {$avgDays} days in screening. Consider adjusting screening thresholds or adding more HR reviewers.",
            'ASSESSMENT' => "Average {$avgDays} days in assessment. Check for cooldown conflicts or low candidate engagement.",
            'INTERVIEW' => "Average {$avgDays} days in interview stage. Consider increasing interviewer availability or panel size.",
            'EVALUATION' => "Average {$avgDays} days in evaluation. Ensure feedback deadlines and consensus meetings are scheduled promptly.",
            'OFFER' => "Average {$avgDays} days in offer stage. Review offer approval workflow and expiry timers.",
            default => "Average {$avgDays} days at {$stage}. Review workflow for optimization opportunities.",
        };
    }
}

