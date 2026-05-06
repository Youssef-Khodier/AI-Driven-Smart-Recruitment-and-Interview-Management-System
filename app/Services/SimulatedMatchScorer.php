<?php

namespace App\Services;

final class SimulatedMatchScorer
{
    public function score(array $job, ?array $candidate): int
    {
        if (! $candidate) {
            return 0;
        }

        $requirements = $this->keywords(($job['requirements'] ?? '') . ' ' . ($job['description'] ?? ''));
        $profile = $this->keywords(($candidate['current_title'] ?? '') . ' ' . ($candidate['skill_keywords'] ?? ''));

        if ($requirements === []) {
            return min(100, 20 + ((int) ($candidate['years_experience'] ?? 0) * 8));
        }

        $matches = count(array_intersect($requirements, $profile));
        $keywordScore = (int) round(($matches / count($requirements)) * 75);
        $experienceScore = min(25, (int) ($candidate['years_experience'] ?? 0) * 5);

        return min(100, $keywordScore + $experienceScore);
    }

    public function scoreWeighted(array $skills, array $candidate): array
    {
        $breakdown = [
            'skills' => [],
            'raw_skill_score' => 0.0,
            'experience_bonus' => 0.0,
            'total_score' => 0
        ];

        $rawScore = 0.0;
        foreach ($skills as $skill) {
            $evidenceField = $skill['evidence_field'] ?? 'skill_keywords';
            $evidenceText = '';
            if ($evidenceField === 'skill_keywords') {
                $evidenceText = ($candidate['current_title'] ?? '') . ' ' . ($candidate['skill_keywords'] ?? '') . ' ' . ($candidate['resume_url'] ?? '');
            } else {
                $evidenceText = (string) ($candidate[$evidenceField] ?? '');
            }

            $profileKeywords = $this->keywords($evidenceText);
            $skillNameWords = $this->keywords($skill['skill_name']);

            $found = false;
            if (!empty($skillNameWords)) {
                $found = count(array_intersect($skillNameWords, $profileKeywords)) > 0;
            }
            if (!$found && stripos($evidenceText, $skill['skill_name']) !== false) {
                $found = true;
            }

            $skillScore = $found ? 1.0 : 0.0;
            $contribution = (float)$skill['weight'] * $skillScore;
            $rawScore += $contribution;

            $breakdown['skills'][] = [
                'name' => $skill['skill_name'],
                'weight' => (float)$skill['weight'],
                'score' => $skillScore,
                'contribution' => $contribution,
                'evidence' => $evidenceField,
                'found' => $found
            ];
        }

        $yearsExp = (int)($candidate['years_experience'] ?? 0);
        $experienceBonus = min(10.0, $yearsExp * 2.0);

        $totalScore = min(100, (int)round($rawScore + $experienceBonus));

        $breakdown['raw_skill_score'] = $rawScore;
        $breakdown['experience_bonus'] = $experienceBonus;
        $breakdown['total_score'] = $totalScore;

        return ['total' => $totalScore, 'breakdown' => $breakdown];
    }

    private function keywords(string $text): array
    {
        $words = preg_split('/[^a-z0-9+#.]+/i', strtolower($text)) ?: [];

        return array_values(array_unique(array_filter($words, fn (string $word): bool => strlen($word) >= 3)));
    }
}
