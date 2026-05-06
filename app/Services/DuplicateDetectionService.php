<?php

namespace App\Services;

use App\Core\Database;
use App\Enums\DuplicateConfidence;

class DuplicateDetectionService {
    public function detectDuplicates(int $jobId): array {
        // Load all candidates for the requisition's applications
        $sql = "SELECT c.*, u.name, u.email 
                FROM applications a
                JOIN candidates c ON a.candidate_id = c.candidate_id
                JOIN users u ON c.candidate_id = u.user_id
                WHERE a.job_id = ?";
        $candidates = Database::fetchAll($sql, [$jobId]);
        
        $sqlDecisions = "SELECT * FROM candidate_merge_log WHERE job_id = ? AND decision_type != 'DEFER'";
        $existingDecisions = Database::fetchAll($sqlDecisions, [$jobId]);
        $resolvedPairs = [];
        foreach ($existingDecisions as $decision) {
            $pair = min($decision['primary_candidate_id'], $decision['duplicate_candidate_id']) . '-' . max($decision['primary_candidate_id'], $decision['duplicate_candidate_id']);
            $resolvedPairs[$pair] = true;
        }

        $suggestions = [];
        $count = count($candidates);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                $cA = $candidates[$i];
                $cB = $candidates[$j];
                
                $idA = $cA['candidate_id'];
                $idB = $cB['candidate_id'];
                $pairKey = min($idA, $idB) . '-' . max($idA, $idB);

                if (isset($resolvedPairs[$pairKey])) {
                    continue;
                }

                $signals = [
                    'HIGH' => 0,
                    'MEDIUM' => 0,
                    'LOW' => 0
                ];
                $evidence = [];

                // 1. Email (Exact case-insensitive)
                if (!empty($cA['email']) && strcasecmp($cA['email'], $cB['email']) === 0) {
                    $signals['HIGH']++;
                    $evidence['email'] = true;
                }

                // 2. Phone (Normalized digits)
                $phoneA = preg_replace('/\D/', '', $cA['phone'] ?? '');
                $phoneB = preg_replace('/\D/', '', $cB['phone'] ?? '');
                if (!empty($phoneA) && $phoneA === $phoneB) {
                    $signals['HIGH']++;
                    $evidence['phone'] = true;
                }

                // 3. Name (Case-insensitive trim)
                if (strcasecmp(trim($cA['name'] ?? ''), trim($cB['name'] ?? '')) === 0) {
                    $signals['MEDIUM']++;
                    $evidence['name'] = true;
                }

                // 4. Title + Experience
                if (!empty($cA['current_title']) && strcasecmp($cA['current_title'], $cB['current_title']) === 0 
                    && (int)$cA['years_experience'] === (int)$cB['years_experience']) {
                    $signals['LOW']++;
                    $evidence['title_experience'] = true;
                }

                // 5. Resume URL
                if (!empty($cA['resume_url']) && $cA['resume_url'] === $cB['resume_url']) {
                    $signals['MEDIUM']++;
                    $evidence['resume_url'] = true;
                }

                if ($signals['HIGH'] > 0 || $signals['MEDIUM'] > 0 || $signals['LOW'] > 0) {
                    $confidence = null;
                    
                    if ($signals['HIGH'] >= 1) {
                        $confidence = DuplicateConfidence::HIGH->value;
                    } elseif ($signals['MEDIUM'] >= 2 || ($signals['MEDIUM'] >= 1 && $signals['LOW'] >= 1)) {
                        $confidence = DuplicateConfidence::MEDIUM->value;
                    } elseif ($signals['MEDIUM'] == 1 || $signals['LOW'] >= 2) {
                        $confidence = DuplicateConfidence::LOW->value;
                    }

                    if ($confidence) {
                        $suggestions[] = [
                            'candidate_a' => $cA,
                            'candidate_b' => $cB,
                            'confidence' => $confidence,
                            'evidence' => $evidence
                        ];
                    }
                }
            }
        }

        return $suggestions;
    }
}
