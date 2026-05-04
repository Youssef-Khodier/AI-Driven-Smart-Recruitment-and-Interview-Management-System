<?php

namespace App\Support;

use App\Enums\AssessmentQuestionType;
use App\Models\CandidateAssessment;
use Carbon\CarbonInterface;
use Illuminate\Support\Str;

class SimulatedAssessmentScorer
{
    public function score(CandidateAssessment $attempt, ?CarbonInterface $savedBefore = null): int
    {
        $attempt->loadMissing(['attemptQuestions.submission']);

        $totalPoints = 0.0;
        $awardedPoints = 0.0;

        foreach ($attempt->attemptQuestions as $question) {
            $points = (float) $question->points;
            $totalPoints += $points;
            $submission = $question->submission;

            if (! $submission || ($savedBefore && $submission->saved_at && $submission->saved_at->gt($savedBefore))) {
                continue;
            }

            $awarded = $this->scoreAnswer($question->question_type, (string) $submission->answer_text, (string) $question->correct_answer, $points);
            $submission->forceFill([
                'is_correct' => $awarded >= $points && $points > 0,
                'awarded_points' => $awarded,
            ])->save();
            $awardedPoints += $awarded;
        }

        if ($totalPoints <= 0) {
            return 0;
        }

        return (int) round(min(100, max(0, ($awardedPoints / $totalPoints) * 100)));
    }

    private function scoreAnswer(AssessmentQuestionType $type, string $answer, string $reference, float $points): float
    {
        $answer = trim($answer);
        $reference = trim($reference);

        if ($answer === '') {
            return 0.0;
        }

        if ($type === AssessmentQuestionType::MCQ) {
            return Str::lower($answer) === Str::lower($reference) ? $points : 0.0;
        }

        if ($reference === '') {
            return $points;
        }

        $keywords = collect(preg_split('/[\s,;|]+/', Str::lower($reference)) ?: [])
            ->map(fn (string $keyword): string => trim($keyword))
            ->filter(fn (string $keyword): bool => strlen($keyword) >= 3)
            ->unique()
            ->values();

        if ($keywords->isEmpty()) {
            return $points;
        }

        $normalizedAnswer = Str::lower($answer);
        $matches = $keywords->filter(fn (string $keyword): bool => str_contains($normalizedAnswer, $keyword))->count();

        return round($points * ($matches / $keywords->count()), 2);
    }
}
