<?php

namespace App\Repositories;

use App\Core\Database;

class DuplicateRepository {
    public function findByPair(int $candidateA, int $candidateB): ?array {
        $primary = min($candidateA, $candidateB);
        $duplicate = max($candidateA, $candidateB);
        
        $sql = "SELECT * FROM candidate_merge_log WHERE primary_candidate_id = ? AND duplicate_candidate_id = ? LIMIT 1";
        return Database::fetch($sql, [$primary, $duplicate]);
    }

    public function recordDecision(int $primaryId, int $duplicateId, int $mergedBy, string $decisionType, string $confidence, ?int $jobId, ?array $matchingEvidence, string $notes): int {
        // According to data-model, the UI handles selecting primary/duplicate
        $sql = "INSERT INTO candidate_merge_log (primary_candidate_id, duplicate_candidate_id, merged_by, decision_type, confidence_category, job_id, matching_evidence, notes)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                decision_type = VALUES(decision_type),
                merged_by = VALUES(merged_by),
                notes = VALUES(notes),
                confidence_category = VALUES(confidence_category),
                job_id = VALUES(job_id),
                matching_evidence = VALUES(matching_evidence)";
                
        Database::query($sql, [
            $primaryId,
            $duplicateId,
            $mergedBy,
            $decisionType,
            $confidence,
            $jobId,
            $matchingEvidence ? json_encode($matchingEvidence) : null,
            $notes
        ]);
        return (int) Database::lastInsertId();
    }

    public function getDecisionsForJob(int $jobId): array {
        $sql = "SELECT * FROM candidate_merge_log WHERE job_id = ?";
        return Database::fetchAll($sql, [$jobId]);
    }
}
