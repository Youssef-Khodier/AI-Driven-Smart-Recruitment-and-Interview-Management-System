<?php

namespace App\Enums;

enum FeedbackGovernanceAuditAction: string
{
    case CALCULATION = 'CALCULATION';
    case FALLBACK_APPLIED = 'FALLBACK_APPLIED';
    case CONCERN_FLAG_CREATED = 'CONCERN_FLAG_CREATED';
    case CONCERN_FLAG_RESOLVED = 'CONCERN_FLAG_RESOLVED';
    case SENTIMENT_SUBMITTED = 'SENTIMENT_SUBMITTED';
    case DEBRIEF_CREATED = 'DEBRIEF_CREATED';
    case DEBRIEF_COMPLETED = 'DEBRIEF_COMPLETED';
    case BENCHMARK_UPDATED = 'BENCHMARK_UPDATED';
    case RECOMMENDATION_RECORDED = 'RECOMMENDATION_RECORDED';
    case OVERRIDE_APPLIED = 'OVERRIDE_APPLIED';

    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
