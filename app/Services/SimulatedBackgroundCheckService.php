<?php

namespace App\Services;

use App\Core\Database;

/**
 * Simulated background check integration.
 * Creates and manages local background check records without external API calls.
 * Statuses: REQUESTED → IN_PROGRESS → PASSED | FAILED | CANCELLED
 */
final class SimulatedBackgroundCheckService
{
    public const STATUS_REQUESTED = 'REQUESTED';
    public const STATUS_IN_PROGRESS = 'IN_PROGRESS';
    public const STATUS_PASSED = 'PASSED';
    public const STATUS_FAILED = 'FAILED';
    public const STATUS_CANCELLED = 'CANCELLED';

    public const CHECK_TYPES = [
        'CRIMINAL_RECORD',
        'EMPLOYMENT_HISTORY',
        'EDUCATION_VERIFICATION',
        'REFERENCE_CHECK',
        'CREDIT_CHECK',
    ];

    /**
     * Request a new background check for a candidate/application.
     *
     * @param int $applicationId
     * @param int $candidateId
     * @param string $checkType One of CHECK_TYPES
     * @param int $requestedBy HR user ID
     * @return int The background_check_id
     */
    public function request(int $applicationId, int $candidateId, string $checkType, int $requestedBy): int
    {
        if (!in_array($checkType, self::CHECK_TYPES)) {
            throw new \InvalidArgumentException("Invalid check type: {$checkType}");
        }

        // Check for existing active check of same type
        $existing = Database::fetch(
            'SELECT background_check_id FROM background_checks WHERE application_id = ? AND check_type = ? AND status NOT IN (?, ?)',
            [$applicationId, $checkType, self::STATUS_PASSED, self::STATUS_CANCELLED]
        );

        if ($existing) {
            throw new \RuntimeException("An active {$checkType} check already exists for this application.");
        }

        return Database::insert('background_checks', [
            'application_id' => $applicationId,
            'candidate_id' => $candidateId,
            'check_type' => $checkType,
            'status' => self::STATUS_REQUESTED,
            'requested_by' => $requestedBy,
            'requested_at' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    /**
     * Advance a check to IN_PROGRESS (simulated).
     */
    public function markInProgress(int $checkId, int $actorId): void
    {
        $check = $this->find($checkId);
        if (!$check || $check['status'] !== self::STATUS_REQUESTED) {
            throw new \RuntimeException('Check cannot be moved to in-progress.');
        }

        Database::update('background_checks', [
            'status' => self::STATUS_IN_PROGRESS,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'background_check_id = ?', [$checkId]);
    }

    /**
     * Complete a check with pass/fail result (simulated).
     */
    public function complete(int $checkId, bool $passed, ?string $notes, int $actorId): void
    {
        $check = $this->find($checkId);
        if (!$check || !in_array($check['status'], [self::STATUS_REQUESTED, self::STATUS_IN_PROGRESS])) {
            throw new \RuntimeException('Check cannot be completed from its current status.');
        }

        Database::update('background_checks', [
            'status' => $passed ? self::STATUS_PASSED : self::STATUS_FAILED,
            'result_notes' => $notes,
            'completed_at' => date('Y-m-d H:i:s'),
            'completed_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'background_check_id = ?', [$checkId]);
    }

    /**
     * Cancel a pending check.
     */
    public function cancel(int $checkId, int $actorId): void
    {
        $check = $this->find($checkId);
        if (!$check || !in_array($check['status'], [self::STATUS_REQUESTED, self::STATUS_IN_PROGRESS])) {
            throw new \RuntimeException('Check cannot be cancelled from its current status.');
        }

        Database::update('background_checks', [
            'status' => self::STATUS_CANCELLED,
            'completed_at' => date('Y-m-d H:i:s'),
            'completed_by' => $actorId,
            'updated_at' => date('Y-m-d H:i:s'),
        ], 'background_check_id = ?', [$checkId]);
    }

    /**
     * Find a background check by ID.
     */
    public function find(int $checkId): ?array
    {
        return Database::fetch('SELECT * FROM background_checks WHERE background_check_id = ?', [$checkId]) ?: null;
    }

    /**
     * Get all checks for an application.
     */
    public function forApplication(int $applicationId): array
    {
        return Database::fetchAll(
            'SELECT bc.*, u.name AS requested_by_name
             FROM background_checks bc
             JOIN users u ON bc.requested_by = u.user_id
             WHERE bc.application_id = ?
             ORDER BY bc.created_at DESC',
            [$applicationId]
        );
    }

    /**
     * Check if all required checks have passed for an application.
     */
    public function allChecksPassed(int $applicationId): bool
    {
        $checks = Database::fetchAll(
            'SELECT status FROM background_checks WHERE application_id = ? AND status != ?',
            [$applicationId, self::STATUS_CANCELLED]
        );

        if (empty($checks)) {
            return false;
        }

        foreach ($checks as $check) {
            if ($check['status'] !== self::STATUS_PASSED) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get summary stats for compliance checks.
     */
    public function summary(): array
    {
        return Database::fetch(
            "SELECT
                COUNT(*) AS total_checks,
                SUM(CASE WHEN status = 'REQUESTED' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'IN_PROGRESS' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'PASSED' THEN 1 ELSE 0 END) AS passed,
                SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) AS failed
             FROM background_checks"
        ) ?: ['total_checks' => 0, 'pending' => 0, 'in_progress' => 0, 'passed' => 0, 'failed' => 0];
    }
}
