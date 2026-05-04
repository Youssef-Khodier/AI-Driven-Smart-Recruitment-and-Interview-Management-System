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

    private function keywords(string $text): array
    {
        $words = preg_split('/[^a-z0-9+#.]+/i', strtolower($text)) ?: [];

        return array_values(array_unique(array_filter($words, fn (string $word): bool => strlen($word) >= 3)));
    }
}
