<?php

namespace Tests\Unit;

use App\Support\SimulatedMatchScorer;
use PHPUnit\Framework\TestCase;

class SimulatedMatchScorerTest extends TestCase
{
    public function test_score_uses_skill_title_and_experience_weighting(): void
    {
        $score = (new SimulatedMatchScorer())->score(
            'Laravel PHP developer with 4 years experience building MVC systems.',
            'Laravel, PHP, MySQL',
            'PHP Developer',
            4
        );

        $this->assertSame(77, $score);
    }

    public function test_score_is_capped_at_one_hundred_for_full_match(): void
    {
        $score = (new SimulatedMatchScorer())->score(
            'Laravel PHP MySQL developer with 3 years experience.',
            'Laravel, PHP, MySQL',
            'Laravel Developer',
            5
        );

        $this->assertSame(100, $score);
    }
}
