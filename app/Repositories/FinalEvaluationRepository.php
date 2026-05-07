<?php

namespace App\Repositories;

use App\Core\Database;
use App\Enums\FinalEvaluationRecommendation;
use App\Enums\FinalEvaluationStatus;

final class FinalEvaluationRepository
{
    public static function getEvidence(int $applicationId): array
    {
        $assessments = Database::fetchAll(
            'SELECT ca.*, a.title, a.type FROM candidate_assessments ca 
             JOIN assessments a ON ca.assessment_id = a.assessment_id 
             WHERE ca.application_id = ? AND ca.status = "COMPLETED" AND ca.score IS NOT NULL',
            [$applicationId]
        );

        $interviews = Database::fetchAll(
            'SELECT f.*, i.interview_type, u.name as interviewer_name FROM interview_feedback f
             JOIN interviews i ON f.interview_id = i.interview_id
             JOIN users u ON f.interviewer_id = u.user_id
             WHERE i.application_id = ?',
            [$applicationId]
        );

        return [
            'assessments' => $assessments,
            'interviews' => $interviews
        ];
    }

    public static function calculateAggregateScore(array $evidence): array
    {
        $assessments = $evidence['assessments'];
        $interviews = $evidence['interviews'];

        $assessmentTotal = 0;
        $assessmentCount = count($assessments);
        foreach ($assessments as $a) {
            // Assessment score is already 0-100 or normalized
            $assessmentTotal += (float)$a['score'];
        }

        $interviewTotal = 0;
        $interviewCount = count($interviews);
        foreach ($interviews as $i) {
            // Interview feedback dimensions are collected on a 0-10 scale.
            $dimensionScore = (
                (float)$i['technical_score'] +
                (float)$i['communication_score'] +
                (float)$i['culture_fit_score'] +
                (float)$i['overall_score']
            ) / 4;
            $interviewTotal += ($dimensionScore / 10) * 100;
        }

        $avgAssessment = $assessmentCount > 0 ? $assessmentTotal / $assessmentCount : null;
        $avgInterview = $interviewCount > 0 ? $interviewTotal / $interviewCount : null;

        $aggregateScore = null;
        if ($avgAssessment !== null && $avgInterview !== null) {
            $aggregateScore = ($avgAssessment + $avgInterview) / 2;
        } elseif ($avgAssessment !== null) {
            $aggregateScore = $avgAssessment;
        } elseif ($avgInterview !== null) {
            $aggregateScore = $avgInterview;
        }

        return [
            'score' => $aggregateScore !== null ? round($aggregateScore, 2) : null,
            'has_partial_evidence' => $assessmentCount === 0 || $interviewCount === 0
        ];
    }

    public static function findByApplicationId(int $applicationId): ?array
    {
        return Database::fetch('SELECT * FROM final_evaluations WHERE application_id = ?', [$applicationId]);
    }

    public static function save(int $applicationId, ?float $aggregateScore, string $recommendation, string $decisionNotes, bool $partialEvidenceAcknowledged, int $evaluatedBy): int
    {
        return Database::transaction(function () use ($applicationId, $aggregateScore, $recommendation, $decisionNotes, $partialEvidenceAcknowledged, $evaluatedBy) {
            $evaluationId = Database::insert('final_evaluations', [
                'application_id' => $applicationId,
                'aggregate_score' => $aggregateScore,
                'recommendation' => $recommendation,
                'status' => self::statusForRecommendation($recommendation),
                'decision_notes' => $decisionNotes,
                'partial_evidence_acknowledged' => (int)$partialEvidenceAcknowledged,
                'evaluated_by' => $evaluatedBy,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            $actor = Database::fetch('SELECT role FROM users WHERE user_id = ?', [$evaluatedBy]);
            Database::insert('compliance_audit_events', [
                'actor_user_id' => $evaluatedBy,
                'actor_role' => $actor['role'] ?? null,
                'entity_type' => 'FINAL_EVALUATION',
                'entity_id' => $evaluationId,
                'action' => 'FINAL_EVALUATION_SCORED',
                'old_values' => null,
                'new_values' => json_encode([
                    'application_id' => $applicationId,
                    'aggregate_score' => $aggregateScore,
                    'recommendation' => $recommendation,
                    'status' => self::statusForRecommendation($recommendation),
                ]),
                'reason' => 'Final evaluation score and recommendation saved.',
                'created_at' => date('Y-m-d H:i:s'),
            ]);

            if ($recommendation === FinalEvaluationRecommendation::NO_HIRE->value) {
                self::updateApplicationStatus($applicationId, 'REJECTED', $evaluatedBy, 'Rejected by final evaluation');
            }

            return $evaluationId;
        });
    }

    public static function updateApplicationStatus(int $applicationId, string $newStatus, int $actorId, ?string $reason = null): void
    {
        $app = Database::fetch('SELECT status FROM applications WHERE application_id = ?', [$applicationId]);
        if (!$app) return;
        $oldStatus = $app['status'];

        if ($oldStatus !== $newStatus) {
            Database::update('applications', ['status' => $newStatus, 'updated_at' => date('Y-m-d H:i:s')], 'application_id = ?', [$applicationId]);
            Database::insert('application_status_histories', [
                'application_id' => $applicationId,
                'actor_user_id' => $actorId,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'reason' => $reason,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
    }

    private static function statusForRecommendation(string $recommendation): string
    {
        return match ($recommendation) {
            FinalEvaluationRecommendation::STRONG_HIRE->value => FinalEvaluationStatus::STRONG_HIRE->value,
            FinalEvaluationRecommendation::HIRE->value => FinalEvaluationStatus::HIRE->value,
            FinalEvaluationRecommendation::HOLD->value => FinalEvaluationStatus::HOLD->value,
            FinalEvaluationRecommendation::NO_HIRE->value => FinalEvaluationStatus::NO_HIRE->value,
            default => FinalEvaluationStatus::EVALUATED->value,
        };
    }
}
