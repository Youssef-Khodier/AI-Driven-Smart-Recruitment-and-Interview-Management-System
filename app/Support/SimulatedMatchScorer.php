<?php

namespace App\Support;

class SimulatedMatchScorer
{
    public function score(string $requirements, ?string $skillKeywords, ?string $currentTitle, int|float|null $yearsExperience): int
    {
        $requirementsText = $this->normalize($requirements);
        $skills = $this->splitKeywords($skillKeywords ?? '');
        $matchedSkills = array_filter($skills, fn (string $skill): bool => str_contains($requirementsText, $this->normalize($skill)));

        $skillScore = count($skills) > 0 ? (count($matchedSkills) / count($skills)) * 70 : 0;
        $titleScore = $this->titleMatches($requirementsText, $currentTitle ?? '') ? 15 : 0;
        $experienceScore = $this->experienceScore($requirementsText, (float) ($yearsExperience ?? 0));

        return max(0, min(100, (int) round($skillScore + $titleScore + $experienceScore)));
    }

    /**
     * @return array<int, string>
     */
    private function splitKeywords(string $keywords): array
    {
        return array_values(array_filter(array_map(
            fn (string $keyword): string => trim($keyword),
            explode(',', $keywords)
        )));
    }

    private function titleMatches(string $requirementsText, string $currentTitle): bool
    {
        $titleWords = preg_split('/\s+/', $this->normalize($currentTitle)) ?: [];
        $titleWords = array_filter($titleWords, fn (string $word): bool => strlen($word) >= 4);

        foreach ($titleWords as $word) {
            if (str_contains($requirementsText, $word)) {
                return true;
            }
        }

        return false;
    }

    private function experienceScore(string $requirementsText, float $yearsExperience): float
    {
        if (! preg_match('/(\d+)\+?\s*(years|yrs)/', $requirementsText, $matches)) {
            return 15;
        }

        $requiredYears = max(1, (int) $matches[1]);

        return min(1, $yearsExperience / $requiredYears) * 15;
    }

    private function normalize(string $value): string
    {
        return strtolower(trim(preg_replace('/[^a-z0-9+#.]+/i', ' ', $value) ?? ''));
    }
}
