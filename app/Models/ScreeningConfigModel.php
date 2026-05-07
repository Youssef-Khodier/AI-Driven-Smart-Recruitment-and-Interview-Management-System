<?php

namespace App\Models;

use App\Core\Database;
use Exception;

class ScreeningConfigModel {
    public function findActiveByJobId(int $jobId): ?array {
        $sql = "SELECT * FROM screening_configs WHERE job_id = ? AND is_active = TRUE LIMIT 1";
        return Database::fetch($sql, [$jobId]);
    }

    public function findByConfigId(int $configId): ?array {
        $sql = "SELECT * FROM screening_configs WHERE config_id = ? LIMIT 1";
        return Database::fetch($sql, [$configId]);
    }

    public function deactivateByJobId(int $jobId): void {
        $sql = "UPDATE screening_configs SET is_active = FALSE WHERE job_id = ?";
        Database::query($sql, [$jobId]);
    }

    public function getSkills(int $configId): array {
        $sql = "SELECT * FROM screening_skills WHERE config_id = ? ORDER BY skill_id ASC";
        return Database::fetchAll($sql, [$configId]);
    }

    public function getThresholds(int $configId): array {
        $sql = "SELECT * FROM screening_thresholds WHERE config_id = ? ORDER BY min_score ASC";
        return Database::fetchAll($sql, [$configId]);
    }

    public function saveConfig(int $jobId, int $createdBy, array $skills, array $thresholds): int {
        Database::beginTransaction();
        try {
            $this->deactivateByJobId($jobId);
            $sql = "INSERT INTO screening_configs (job_id, created_by, is_active) VALUES (?, ?, TRUE)";
            Database::query($sql, [$jobId, $createdBy]);
            $configId = (int) Database::lastInsertId();

            foreach ($skills as $skill) {
                $sqlSkill = "INSERT INTO screening_skills (config_id, skill_name, weight, evidence_field) VALUES (?, ?, ?, ?)";
                Database::query($sqlSkill, [
                    $configId,
                    $skill['skill_name'],
                    $skill['weight'],
                    $skill['evidence_field'] ?? 'skill_keywords'
                ]);
            }

            foreach ($thresholds as $threshold) {
                $sqlThresh = "INSERT INTO screening_thresholds (config_id, min_score, max_score, target_status) VALUES (?, ?, ?, ?)";
                Database::query($sqlThresh, [
                    $configId,
                    $threshold['min_score'],
                    $threshold['max_score'],
                    $threshold['target_status']
                ]);
            }

            Database::commit();
            return $configId;
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
