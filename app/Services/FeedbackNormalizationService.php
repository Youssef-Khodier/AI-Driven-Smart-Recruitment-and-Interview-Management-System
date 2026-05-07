<?php

namespace App\Services;

final class FeedbackNormalizationService
{
    private const MIN_COMPARABLE_SUBMISSIONS = 5;
    private const SCORE_DIMENSIONS = ['technical_score', 'communication_score', 'culture_fit_score', 'overall_score'];
    private const DIMENSION_MAX = 10.0;
    private const AGGREGATE_MAX = 100.0;

    /**
     * Calculate normalized scores from raw feedback using interviewer harshness history.
     *
     * @param array $rawFeedback Array of official feedback rows
     * @param array $interviewerHistories Map of interviewer_id to their harshness history
     * @return array [
     *   'raw_score_summary' => array,
     *   'normalized_score_summary' => array,
     *   'aggregate_score' => float,
     *   'recommendation' => string,
     *   'fallback_reasons' => array,
     *   'normalization_status' => string,
     *   'included_feedback_count' => int,
     *   'missing_feedback_count' => int
     * ]
     */
    public function calculate(array $rawFeedback, array $interviewerHistories, int $missingCount = 0): array
    {
        $rawSummary = [];
        $normalizedSummary = [];
        $fallbackReasons = [];

        if (empty($rawFeedback)) {
            return [
                'raw_score_summary' => [],
                'normalized_score_summary' => [],
                'aggregate_score' => 0.0,
                'recommendation' => 'NO_HIRE',
                'fallback_reasons' => ['No feedback submitted'],
                'normalization_status' => 'RAW_FALLBACK',
                'included_feedback_count' => 0,
                'missing_feedback_count' => $missingCount,
            ];
        }

        $allNormalized = true;

        foreach ($rawFeedback as $feedback) {
            $interviewerId = (int)$feedback['interviewer_id'];
            $history = $interviewerHistories[$interviewerId] ?? [];

            $raw = [];
            $normalized = [];

            foreach (self::SCORE_DIMENSIONS as $dim) {
                $rawVal = (float)($feedback[$dim] ?? 0);
                $raw[$dim] = $rawVal;

                if (count($history) >= self::MIN_COMPARABLE_SUBMISSIONS) {
                    // Calculate this interviewer's historical average for this dimension
                    $histSum = 0.0;
                    foreach ($history as $h) {
                        $histSum += (float)($h[$dim] ?? 0);
                    }
                    $histAvg = $histSum / count($history);

                    // Global average is assumed as midpoint (5.0) for normalization
                    $globalAvg = self::DIMENSION_MAX / 2.0;

                    // Harshness factor: if interviewer avg is 3.0 and global is 5.0, factor = 5/3 = 1.67
                    // Leniency factor: if interviewer avg is 8.0 and global is 5.0, factor = 5/8 = 0.625
                    $factor = $histAvg > 0 ? $globalAvg / $histAvg : 1.0;

                    // Clamp factor to avoid extreme adjustments
                    $factor = max(0.5, min(2.0, $factor));

                    $normalizedVal = round(min(self::DIMENSION_MAX, $rawVal * $factor), 2);
                    $normalized[$dim] = $normalizedVal;
                } else {
                    // Not enough history — use raw scores as fallback
                    $normalized[$dim] = $rawVal;
                    $allNormalized = false;

                    if (!in_array("Interviewer #{$interviewerId}: insufficient history (" . count($history) . " < " . self::MIN_COMPARABLE_SUBMISSIONS . ")", $fallbackReasons)) {
                        $fallbackReasons[] = "Interviewer #{$interviewerId}: insufficient history (" . count($history) . " < " . self::MIN_COMPARABLE_SUBMISSIONS . ")";
                    }
                }
            }

            $rawSummary[$interviewerId] = $raw;
            $normalizedSummary[$interviewerId] = $normalized;
        }

        // Aggregate: average across all interviewers, then scale to 0-100
        $dimensionAverages = [];
        foreach (self::SCORE_DIMENSIONS as $dim) {
            $sum = 0.0;
            $count = 0;
            foreach ($normalizedSummary as $ns) {
                $sum += $ns[$dim];
                $count++;
            }
            $dimensionAverages[$dim] = $count > 0 ? round($sum / $count, 2) : 0;
        }

        // Weighted aggregate: overall_score counts 40%, other three count 20% each
        $aggregate = round(
            ($dimensionAverages['technical_score'] * 0.2 +
             $dimensionAverages['communication_score'] * 0.2 +
             $dimensionAverages['culture_fit_score'] * 0.2 +
             $dimensionAverages['overall_score'] * 0.4) / self::DIMENSION_MAX * self::AGGREGATE_MAX,
            2
        );

        // Determine recommendation based on aggregate score
        $recommendation = $this->recommendFromScore($aggregate);

        // Determine normalization status
        if ($allNormalized && empty($fallbackReasons)) {
            $status = 'APPLIED';
        } elseif (!$allNormalized && count($normalizedSummary) > 0) {
            $status = 'PARTIAL';
        } else {
            $status = 'RAW_FALLBACK';
        }

        if ($missingCount > 0) {
            $fallbackReasons[] = "{$missingCount} assigned interviewer(s) have not submitted feedback";
            if ($status === 'APPLIED') {
                $status = 'PARTIAL';
            }
        }

        return [
            'raw_score_summary' => $rawSummary,
            'normalized_score_summary' => array_merge($normalizedSummary, ['averages' => $dimensionAverages]),
            'aggregate_score' => $aggregate,
            'recommendation' => $recommendation,
            'fallback_reasons' => $fallbackReasons,
            'normalization_status' => $status,
            'included_feedback_count' => count($rawFeedback),
            'missing_feedback_count' => $missingCount,
        ];
    }

    private function recommendFromScore(float $score): string
    {
        if ($score >= 80) {
            return 'STRONG_HIRE';
        }
        if ($score >= 60) {
            return 'HIRE';
        }
        if ($score >= 40) {
            return 'HOLD';
        }
        return 'NO_HIRE';
    }
}
