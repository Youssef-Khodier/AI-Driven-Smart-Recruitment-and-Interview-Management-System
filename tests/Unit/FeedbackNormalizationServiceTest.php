<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FeedbackNormalizationService;
use PHPUnit\Framework\TestCase;

final class FeedbackNormalizationServiceTest extends TestCase
{
    public function testItReturnsNoHireFallbackWhenNoFeedbackExists(): void
    {
        $result = (new FeedbackNormalizationService())->calculate([], [], 2);

        $this->assertSame(0.0, $result['aggregate_score']);
        $this->assertSame('NO_HIRE', $result['recommendation']);
        $this->assertSame('RAW_FALLBACK', $result['normalization_status']);
        $this->assertSame(['No feedback submitted'], $result['fallback_reasons']);
        $this->assertSame(2, $result['missing_feedback_count']);
    }

    public function testItUsesRawScoresWhenInterviewerHistoryIsInsufficient(): void
    {
        $result = (new FeedbackNormalizationService())->calculate([
            $this->feedback(7, 6, 5, 8),
        ], [
            1 => [
                $this->history(5, 5, 5, 5),
            ],
        ]);

        $this->assertSame(68.0, $result['aggregate_score']);
        $this->assertSame('HIRE', $result['recommendation']);
        $this->assertSame('PARTIAL', $result['normalization_status']);
        $this->assertSame(1, $result['included_feedback_count']);
    }

    public function testItAppliesNormalizationWhenEnoughHistoryExists(): void
    {
        $history = array_fill(0, 5, $this->history(2, 2, 2, 2));

        $result = (new FeedbackNormalizationService())->calculate([
            $this->feedback(4, 4, 4, 4),
        ], [
            1 => $history,
        ]);

        $this->assertSame(80.0, $result['aggregate_score']);
        $this->assertSame('STRONG_HIRE', $result['recommendation']);
        $this->assertSame('APPLIED', $result['normalization_status']);
        $this->assertSame([], $result['fallback_reasons']);
    }

    private function feedback(float $technical, float $communication, float $culture, float $overall): array
    {
        return [
            'interviewer_id' => 1,
            'technical_score' => $technical,
            'communication_score' => $communication,
            'culture_fit_score' => $culture,
            'overall_score' => $overall,
        ];
    }

    private function history(float $technical, float $communication, float $culture, float $overall): array
    {
        return [
            'technical_score' => $technical,
            'communication_score' => $communication,
            'culture_fit_score' => $culture,
            'overall_score' => $overall,
        ];
    }
}
