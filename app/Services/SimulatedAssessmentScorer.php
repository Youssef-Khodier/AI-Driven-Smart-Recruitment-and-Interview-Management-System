<?php

namespace App\Services;

use App\Core\Database;

final class SimulatedAssessmentScorer
{
    public function score(int $attemptId, ?string $savedBefore = null): int
    {
        $questions = Database::fetchAll(
            'SELECT aq.*, s.submission_id, s.answer_text, s.saved_at
             FROM candidate_assessment_questions aq
             LEFT JOIN submissions s ON s.attempt_question_id = aq.attempt_question_id AND s.ca_id = aq.ca_id
             WHERE aq.ca_id = ?
             ORDER BY aq.display_order',
            [$attemptId]
        );

        $total = 0.0;
        $awarded = 0.0;

        foreach ($questions as $question) {
            $points = (float) $question['points'];
            $total += $points;

            if (! $question['submission_id'] || ($savedBefore && $question['saved_at'] && $question['saved_at'] > $savedBefore)) {
                continue;
            }

            $questionAwarded = $this->scoreAnswer(
                $question['question_type'],
                (string) $question['answer_text'],
                (string) $question['correct_answer'],
                $points
            );

            Database::update('submissions', [
                'is_correct' => $questionAwarded >= $points && $points > 0 ? 1 : 0,
                'awarded_points' => $questionAwarded,
                'updated_at' => date('Y-m-d H:i:s'),
            ], 'submission_id = ?', [(int) $question['submission_id']]);

            $awarded += $questionAwarded;
        }

        return $total > 0 ? (int) round(min(100, max(0, ($awarded / $total) * 100))) : 0;
    }

    private function scoreAnswer(string $type, string $answer, string $reference, float $points): float
    {
        $answer = trim($answer);
        $reference = trim($reference);

        if ($answer === '') {
            return 0.0;
        }

        if ($type === 'MCQ') {
            return strtolower($answer) === strtolower($reference) ? $points : 0.0;
        }

        if ($reference === '') {
            return $points;
        }

        $keywords = array_values(array_unique(array_filter(
            preg_split('/[\s,;|]+/', strtolower($reference)) ?: [],
            fn (string $keyword): bool => strlen(trim($keyword)) >= 3
        )));

        if ($keywords === []) {
            return $points;
        }

        $matches = 0;
        $normalized = strtolower($answer);
        foreach ($keywords as $keyword) {
            if (str_contains($normalized, trim($keyword))) {
                $matches++;
            }
        }

        return round($points * ($matches / count($keywords)), 2);
    }
}
