<?php

namespace App\Models;

use App\Core\Database;
use App\Enums\FeedbackGovernanceAuditAction;
use App\Enums\FeedbackConcernStatus;
use App\Enums\EvaluationDebriefStatus;
use App\Enums\CompetencyGapSeverity;
use App\Services\FeedbackNormalizationService;

final class FeedbackGovernanceModel
{
    public static function refreshForInterview(int $interviewId, int $actorUserId, string $actorRole): ?int
    {
        $interview = Database::fetch(
            'SELECT i.interview_id, i.application_id, a.candidate_id, a.job_id
             FROM interviews i
             JOIN applications a ON a.application_id = i.application_id
             WHERE i.interview_id = ?',
            [$interviewId]
        );

        if (!$interview) {
            return null;
        }

        $feedback = Database::fetchAll(
            "SELECT f.*
             FROM interview_feedback f
             JOIN interviewers_assignment ia
               ON ia.interview_id = f.interview_id
              AND ia.interviewer_id = f.interviewer_id
             WHERE f.interview_id = ?
               AND ia.is_shadowing = 0
               AND ia.role_in_panel IN ('INTERVIEWER', 'PANEL_LEAD')
             ORDER BY f.submitted_at ASC",
            [$interviewId]
        );

        $completeness = self::getOfficialFeedbackCompleteness($interviewId);
        $histories = [];
        foreach ($feedback as $row) {
            $histories[(int)$row['interviewer_id']] = self::getInterviewerHarshnessHistory((int)$row['interviewer_id']);
        }

        $result = (new FeedbackNormalizationService())->calculate($feedback, $histories, (int)$completeness['missing']);
        $snapshotId = self::createSnapshot([
            'application_id' => (int)$interview['application_id'],
            'interview_id' => $interviewId,
            'calculated_by' => $actorUserId,
            ...$result,
        ]);

        self::ensureDefaultBenchmarks((int)$interview['job_id'], $actorUserId);
        self::generateGapSnapshots($snapshotId, (int)$interview['job_id']);

        self::recordAudit([
            'actor_user_id' => $actorUserId,
            'actor_role' => $actorRole,
            'application_id' => (int)$interview['application_id'],
            'interview_id' => $interviewId,
            'entity_type' => 'normalized_evaluation_snapshots',
            'entity_id' => $snapshotId,
            'action' => empty($result['fallback_reasons'])
                ? FeedbackGovernanceAuditAction::CALCULATION->value
                : FeedbackGovernanceAuditAction::FALLBACK_APPLIED->value,
            'new_values' => [
                'aggregate_score' => $result['aggregate_score'],
                'recommendation' => $result['recommendation'],
                'normalization_status' => $result['normalization_status'],
            ],
        ]);

        if ((int)$completeness['missing'] === 0 && (int)$completeness['included'] > 0 && !self::getDebriefForInterview($interviewId)) {
            $participants = array_map(
                fn($row) => (int)$row['interviewer_id'],
                $feedback
            );
            $debriefId = self::createDebrief([
                'application_id' => (int)$interview['application_id'],
                'interview_id' => $interviewId,
                'participants' => $participants,
            ]);

            self::recordAudit([
                'actor_user_id' => $actorUserId,
                'actor_role' => $actorRole,
                'application_id' => (int)$interview['application_id'],
                'interview_id' => $interviewId,
                'entity_type' => 'evaluation_debrief_records',
                'entity_id' => $debriefId,
                'action' => FeedbackGovernanceAuditAction::DEBRIEF_CREATED->value,
                'reason' => 'All required interview feedback has been submitted.',
            ]);
        }

        return $snapshotId;
    }

    // --- Snapshots ---
    public static function createSnapshot(array $data): int
    {
        return Database::insert('normalized_evaluation_snapshots', [
            'application_id' => $data['application_id'],
            'interview_id' => $data['interview_id'] ?? null,
            'calculated_by' => $data['calculated_by'] ?? null,
            'raw_score_summary' => json_encode($data['raw_score_summary'] ?? []),
            'normalized_score_summary' => json_encode($data['normalized_score_summary'] ?? []),
            'aggregate_score' => $data['aggregate_score'] ?? 0,
            'recommendation' => $data['recommendation'] ?? 'NO_HIRE',
            'normalization_status' => $data['normalization_status'] ?? 'RAW_FALLBACK',
            'fallback_reasons' => isset($data['fallback_reasons']) ? json_encode($data['fallback_reasons']) : null,
            'included_feedback_count' => $data['included_feedback_count'] ?? 0,
            'missing_feedback_count' => $data['missing_feedback_count'] ?? 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getLatestSnapshot(int $applicationId): ?array
    {
        $row = Database::fetch(
            'SELECT * FROM normalized_evaluation_snapshots WHERE application_id = ? ORDER BY created_at DESC LIMIT 1',
            [$applicationId]
        );

        if ($row) {
            $row['raw_score_summary'] = json_decode($row['raw_score_summary'] ?? '[]', true);
            $row['normalized_score_summary'] = json_decode($row['normalized_score_summary'] ?? '[]', true);
            $row['fallback_reasons'] = json_decode($row['fallback_reasons'] ?? '[]', true);
        }

        return $row ?: null;
    }

    public static function getOfficialFeedbackCompleteness(int $interviewId): array
    {
        $assignments = Database::fetchAll(
            "SELECT interviewer_id, role_in_panel, is_shadowing
             FROM interviewers_assignment
             WHERE interview_id = ?",
            [$interviewId]
        );

        $officialIds = [];
        foreach ($assignments as $a) {
            if (!$a['is_shadowing'] && in_array($a['role_in_panel'], ['INTERVIEWER', 'PANEL_LEAD'])) {
                $officialIds[] = (int)$a['interviewer_id'];
            }
        }

        $feedbackRows = Database::fetchAll(
            "SELECT interviewer_id FROM interview_feedback WHERE interview_id = ?",
            [$interviewId]
        );
        $submittedIds = array_map(fn($r) => (int)$r['interviewer_id'], $feedbackRows);

        $included = count(array_intersect($officialIds, $submittedIds));
        $missing = count($officialIds) - $included;

        return ['included' => $included, 'missing' => max(0, $missing)];
    }

    public static function getInterviewerHarshnessHistory(int $interviewerId): array
    {
        return Database::fetchAll(
            "SELECT f.overall_score, f.technical_score, f.communication_score, f.culture_fit_score,
                    f.interview_id, f.submitted_at
             FROM interview_feedback f
             WHERE f.interviewer_id = ?
             ORDER BY f.submitted_at DESC
             LIMIT 50",
            [$interviewerId]
        );
    }

    // --- Flags ---
    public static function createConcernFlag(array $data): int
    {
        return Database::insert('feedback_concern_flags', [
            'application_id' => $data['application_id'],
            'interview_id' => $data['interview_id'] ?? null,
            'candidate_id' => $data['candidate_id'],
            'category' => $data['category'],
            'severity' => $data['severity'] ?? 'MEDIUM',
            'explanation' => $data['explanation'],
            'status' => FeedbackConcernStatus::OPEN->value,
            'created_by' => $data['created_by'],
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getOpenFlagsForApplication(int $applicationId): array
    {
        return Database::fetchAll(
            'SELECT f.*, u.name AS created_by_name
             FROM feedback_concern_flags f
             JOIN users u ON f.created_by = u.user_id
             WHERE f.application_id = ? AND f.status = ?
             ORDER BY f.created_at DESC',
            [$applicationId, FeedbackConcernStatus::OPEN->value]
        );
    }

    public static function resolveConcernFlag(int $flagId, array $data): void
    {
        Database::update('feedback_concern_flags', [
            'status' => $data['status'],
            'resolved_by' => $data['resolved_by'],
            'resolved_at' => date('Y-m-d H:i:s'),
            'resolution_rationale' => $data['resolution_rationale'],
        ], 'flag_id = ?', [$flagId]);
    }

    // --- Sentiment ---
    public static function createSentiment(array $data): int
    {
        return Database::insert('candidate_interview_sentiment', [
            'candidate_id' => $data['candidate_id'],
            'application_id' => $data['application_id'],
            'interview_id' => $data['interview_id'],
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'submitted_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function hasSubmittedSentiment(int $candidateId, int $interviewId): bool
    {
        $result = Database::fetch(
            'SELECT COUNT(*) AS count FROM candidate_interview_sentiment WHERE candidate_id = ? AND interview_id = ?',
            [$candidateId, $interviewId]
        );
        return (int)($result['count'] ?? 0) > 0;
    }

    public static function getSentimentForInterview(int $interviewId): ?array
    {
        return Database::fetch(
            'SELECT * FROM candidate_interview_sentiment WHERE interview_id = ?',
            [$interviewId]
        ) ?: null;
    }

    // --- Debriefs ---
    public static function createDebrief(array $data): int
    {
        return Database::insert('evaluation_debrief_records', [
            'application_id' => $data['application_id'],
            'interview_id' => $data['interview_id'],
            'status' => EvaluationDebriefStatus::PENDING->value ?? 'PENDING',
            'participants' => isset($data['participants']) ? json_encode($data['participants']) : null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public static function getDebriefForInterview(int $interviewId): ?array
    {
        $row = Database::fetch(
            'SELECT * FROM evaluation_debrief_records WHERE interview_id = ?',
            [$interviewId]
        );

        if ($row) {
            $row['participants'] = json_decode($row['participants'] ?? '[]', true);
        }

        return $row ?: null;
    }

    public static function updateDebrief(int $debriefId, array $data): void
    {
        $update = [];
        foreach (['status', 'consensus_level', 'dissent_notes', 'final_recommendation', 'rationale', 'next_action', 'completed_by', 'completed_at'] as $field) {
            if (array_key_exists($field, $data)) {
                $update[$field] = $data[$field];
            }
        }
        if (isset($data['participants'])) {
            $update['participants'] = json_encode($data['participants']);
        }
        if (!empty($update)) {
            Database::update('evaluation_debrief_records', $update, 'debrief_id = ?', [$debriefId]);
        }
    }

    public static function blockDebriefsForApplication(int $applicationId): void
    {
        Database::execute(
            'UPDATE evaluation_debrief_records SET status = ? WHERE application_id = ? AND status = ?',
            ['BLOCKED', $applicationId, 'PENDING']
        );
    }

    public static function unblockDebriefsForApplication(int $applicationId): void
    {
        Database::execute(
            'UPDATE evaluation_debrief_records SET status = ? WHERE application_id = ? AND status = ?',
            ['PENDING', $applicationId, 'BLOCKED']
        );
    }

    // --- Benchmarks ---
    public static function getBenchmarksForJob(int $jobId): array
    {
        return Database::fetchAll(
            'SELECT * FROM job_competency_benchmarks WHERE job_id = ? ORDER BY competency',
            [$jobId]
        );
    }

    public static function updateBenchmark(int $jobId, string $competency, array $data): void
    {
        $existing = Database::fetch(
            'SELECT benchmark_id FROM job_competency_benchmarks WHERE job_id = ? AND competency = ?',
            [$jobId, $competency]
        );

        if ($existing) {
            Database::update('job_competency_benchmarks', [
                'benchmark_score' => $data['benchmark_score'],
                'weight' => $data['weight'] ?? null,
                'source' => $data['source'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'benchmark_id = ?', [$existing['benchmark_id']]);
        } else {
            Database::insert('job_competency_benchmarks', [
                'job_id' => $jobId,
                'competency' => $competency,
                'benchmark_score' => $data['benchmark_score'],
                'weight' => $data['weight'] ?? null,
                'source' => $data['source'] ?? null,
                'updated_by' => $data['updated_by'] ?? null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public static function ensureDefaultBenchmarks(int $jobId, ?int $actorUserId = null): void
    {
        if (!empty(self::getBenchmarksForJob($jobId))) {
            return;
        }

        foreach ([
            'technical' => 7.0,
            'communication' => 7.0,
            'culture_fit' => 7.0,
        ] as $competency => $score) {
            self::updateBenchmark($jobId, $competency, [
                'benchmark_score' => $score,
                'weight' => 1.0,
                'source' => 'DEFAULT_FEEDBACK_BENCHMARK',
                'updated_by' => $actorUserId,
            ]);
        }
    }


    // --- Gaps ---
    public static function generateGapSnapshots(int $snapshotId, int $jobId): void
    {
        $benchmarks = self::getBenchmarksForJob($jobId);
        $snapshot = Database::fetch('SELECT * FROM normalized_evaluation_snapshots WHERE snapshot_id = ?', [$snapshotId]);

        if (!$snapshot || empty($benchmarks)) {
            return;
        }

        $normalized = json_decode($snapshot['normalized_score_summary'] ?? '{}', true);

        $competencyMap = [
            'technical' => 'technical_score',
            'communication' => 'communication_score',
            'culture_fit' => 'culture_fit_score',
        ];

        foreach ($benchmarks as $benchmark) {
            $key = strtolower($benchmark['competency']);
            $candidateScore = 0.0;

            if (isset($competencyMap[$key]) && isset($normalized[$competencyMap[$key]])) {
                $candidateScore = (float)$normalized[$competencyMap[$key]];
            } elseif (isset($normalized[$key])) {
                $candidateScore = (float)$normalized[$key];
            }

            $benchmarkScore = (float)$benchmark['benchmark_score'];
            $gapRatio = $benchmarkScore > 0 ? round(($benchmarkScore - $candidateScore) / $benchmarkScore, 2) : 0;

            if ($gapRatio <= 0.1) {
                $severity = 'MEETS';
            } elseif ($gapRatio <= 0.3) {
                $severity = 'MINOR_GAP';
            } elseif ($gapRatio <= 0.5) {
                $severity = 'MODERATE_GAP';
            } else {
                $severity = 'CRITICAL_GAP';
            }

            Database::insert('competency_gap_snapshots', [
                'snapshot_id' => $snapshotId,
                'benchmark_id' => $benchmark['benchmark_id'],
                'competency' => $benchmark['competency'],
                'candidate_score' => $candidateScore,
                'benchmark_score' => $benchmarkScore,
                'gap_ratio' => $gapRatio,
                'severity' => $severity,
            ]);
        }
    }

    public static function getGapSnapshots(int $snapshotId): array
    {
        return Database::fetchAll(
            'SELECT g.*, b.weight, b.source
             FROM competency_gap_snapshots g
             JOIN job_competency_benchmarks b ON g.benchmark_id = b.benchmark_id
             WHERE g.snapshot_id = ?
             ORDER BY g.gap_ratio DESC',
            [$snapshotId]
        );
    }

    // --- Audit ---
    public static function recordAudit(array $data): int
    {
        return Database::insert('feedback_governance_audit_records', [
            'actor_user_id' => $data['actor_user_id'] ?? null,
            'actor_role' => $data['actor_role'] ?? null,
            'application_id' => $data['application_id'] ?? null,
            'interview_id' => $data['interview_id'] ?? null,
            'entity_type' => $data['entity_type'],
            'entity_id' => $data['entity_id'],
            'action' => $data['action'],
            'old_values' => isset($data['old_values']) ? json_encode($data['old_values']) : null,
            'new_values' => isset($data['new_values']) ? json_encode($data['new_values']) : null,
            'reason' => $data['reason'] ?? null,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }
}
