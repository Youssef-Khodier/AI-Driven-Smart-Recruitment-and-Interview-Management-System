<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\SimulatedMatchScorer;
use PHPUnit\Framework\TestCase;

final class SimulatedMatchScorerTest extends TestCase
{
    public function testItReturnsZeroWhenCandidateIsMissing(): void
    {
        $score = (new SimulatedMatchScorer())->score(
            ['requirements' => 'PHP Laravel SQL'],
            null
        );

        $this->assertSame(0, $score);
    }

    public function testItScoresKeywordMatchesAndExperience(): void
    {
        $score = (new SimulatedMatchScorer())->score(
            ['requirements' => 'PHP SQL Docker'],
            ['current_title' => 'PHP Developer', 'skill_keywords' => 'SQL APIs', 'years_experience' => 3]
        );

        $this->assertSame(65, $score);
    }

    public function testItUsesExperienceFallbackWhenJobHasNoRequirements(): void
    {
        $score = (new SimulatedMatchScorer())->score(
            ['requirements' => '', 'description' => ''],
            ['skill_keywords' => 'anything', 'years_experience' => 20]
        );

        $this->assertSame(100, $score);
    }

    public function testWeightedScoringReturnsBreakdown(): void
    {
        $result = (new SimulatedMatchScorer())->scoreWeighted(
            [
                ['skill_name' => 'PHP', 'weight' => 40, 'evidence_field' => 'skill_keywords'],
                ['skill_name' => 'Leadership', 'weight' => 30, 'evidence_field' => 'current_title'],
                ['skill_name' => 'Kubernetes', 'weight' => 20, 'evidence_field' => 'skill_keywords'],
            ],
            [
                'current_title' => 'Engineering Leadership',
                'skill_keywords' => 'PHP SQL APIs',
                'years_experience' => 4,
            ]
        );

        $this->assertSame(78, $result['total']);
        $this->assertSame(70.0, $result['breakdown']['raw_skill_score']);
        $this->assertSame(8.0, $result['breakdown']['experience_bonus']);
        $this->assertTrue($result['breakdown']['skills'][0]['found']);
        $this->assertTrue($result['breakdown']['skills'][1]['found']);
        $this->assertFalse($result['breakdown']['skills'][2]['found']);
    }
}
