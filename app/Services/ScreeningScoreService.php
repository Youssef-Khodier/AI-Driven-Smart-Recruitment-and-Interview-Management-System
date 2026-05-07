<?php

namespace App\Services;

use App\Models\ScreeningConfigModel;
use App\Models\ScreeningAuditModel;
use App\Core\Database;
use App\Enums\ScreeningAuditAction;
use Exception;

class ScreeningScoreService {
    private ScreeningConfigModel $configRepo;
    private SimulatedMatchScorer $scorer;
    private ScreeningAuditModel $auditRepo;

    public function __construct() {
        $this->configRepo = new ScreeningConfigModel();
        $this->scorer = new SimulatedMatchScorer();
        $this->auditRepo = new ScreeningAuditModel();
    }

    public function recalculateForJob(int $jobId, int $actorId): array {
        $config = $this->configRepo->findActiveByJobId($jobId);
        if (!$config) {
            throw new Exception("No active screening configuration found for this requisition.");
        }

        $skills = $this->configRepo->getSkills($config['config_id']);
        
        $sql = "SELECT a.*, c.current_title, c.skill_keywords, c.resume_url, c.years_experience
                FROM applications a
                JOIN candidates c ON a.candidate_id = c.candidate_id
                WHERE a.job_id = ? AND a.status = 'APPLIED'";
        $applications = Database::fetchAll($sql, [$jobId]);

        Database::beginTransaction();
        try {
            $updatedCount = 0;
            foreach ($applications as $app) {
                $candidateData = [
                    'current_title' => $app['current_title'],
                    'skill_keywords' => $app['skill_keywords'],
                    'resume_url' => $app['resume_url'],
                    'years_experience' => $app['years_experience']
                ];
                
                $result = $this->scorer->scoreWeighted($skills, $candidateData);
                
                $updateSql = "UPDATE applications SET match_score = ?, match_score_breakdown = ? WHERE application_id = ?";
                Database::query($updateSql, [
                    $result['total'],
                    json_encode($result['breakdown']),
                    $app['application_id']
                ]);
                $updatedCount++;
            }

            $this->auditRepo->log(
                $jobId, 
                $actorId, 
                ScreeningAuditAction::SCORES_RECALCULATED->value, 
                'CONFIG', 
                $config['config_id'], 
                null, 
                ['applications_scored' => $updatedCount, 'config_id' => $config['config_id']]
            );

            Database::commit();
            return ['updated_count' => $updatedCount];
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }

    public function getShortlist(int $jobId): array {
        $sql = "SELECT a.*, c.phone, c.current_title, c.years_experience, u.name, u.email
                FROM applications a
                JOIN candidates c ON a.candidate_id = c.candidate_id
                JOIN users u ON c.candidate_id = u.user_id
                WHERE a.job_id = ?
                ORDER BY a.match_score DESC, c.years_experience DESC, a.applied_at ASC";
        return Database::fetchAll($sql, [$jobId]);
    }

    public function executeTriage(int $jobId, int $actorId): array {
        $config = $this->configRepo->findActiveByJobId($jobId);
        if (!$config) {
            throw new Exception("No active screening configuration found for this requisition.");
        }

        $thresholds = $this->configRepo->getThresholds($config['config_id']);
        
        $sql = "SELECT application_id, status, match_score 
                FROM applications 
                WHERE job_id = ? AND status = 'APPLIED'";
        $applications = Database::fetchAll($sql, [$jobId]);

        Database::beginTransaction();
        try {
            $changes = [];
            $summary = [
                'SCREENING' => 0,
                'ASSESSMENT' => 0,
                'INTERVIEW' => 0,
                'REJECTED' => 0
            ];

            foreach ($applications as $app) {
                $score = (int)$app['match_score'];
                $targetStatus = null;
                $matchedThreshold = null;

                foreach ($thresholds as $t) {
                    if ($score >= (int)$t['min_score'] && $score <= (int)$t['max_score']) {
                        $targetStatus = $t['target_status'];
                        $matchedThreshold = $t;
                        break;
                    }
                }

                if ($targetStatus && $targetStatus !== $app['status']) {
                    $updateSql = "UPDATE applications SET status = ? WHERE application_id = ?";
                    Database::query($updateSql, [$targetStatus, $app['application_id']]);

                    $reason = sprintf(
                        "Simulated automated triage: Score %d matched threshold band %d-%d",
                        $score,
                        $matchedThreshold['min_score'],
                        $matchedThreshold['max_score']
                    );

                    $historySql = "INSERT INTO application_status_histories (application_id, actor_user_id, old_status, new_status, reason)
                                   VALUES (?, ?, ?, ?, ?)";
                    Database::query($historySql, [
                        $app['application_id'],
                        $actorId,
                        $app['status'],
                        $targetStatus,
                        $reason
                    ]);

                    $this->auditRepo->log(
                        $jobId,
                        $actorId,
                        ScreeningAuditAction::TRIAGE_STATUS_CHANGE->value,
                        'APPLICATION',
                        $app['application_id'],
                        ['status' => $app['status']],
                        ['status' => $targetStatus, 'score' => $score, 'threshold' => $matchedThreshold]
                    );

                    $summary[$targetStatus]++;
                    $changes[] = [
                        'application_id' => $app['application_id'],
                        'old_status' => $app['status'],
                        'new_status' => $targetStatus,
                        'score' => $score
                    ];
                }
            }

            if (!empty($changes)) {
                $this->auditRepo->log(
                    $jobId,
                    $actorId,
                    ScreeningAuditAction::TRIAGE_EXECUTED->value,
                    'CONFIG',
                    $config['config_id'],
                    null,
                    ['total_moved' => count($changes), 'summary' => $summary]
                );
            }

            Database::commit();
            return ['changes' => $changes, 'summary' => $summary];
        } catch (Exception $e) {
            Database::rollBack();
            throw $e;
        }
    }
}
