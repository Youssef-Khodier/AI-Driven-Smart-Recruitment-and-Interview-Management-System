<?php

namespace App\Enums;

enum ComplianceAuditAction: string
{
    case REPORT_GENERATED = 'REPORT_GENERATED';
    case RUN_CHECK_EXECUTED = 'RUN_CHECK_EXECUTED';
    case ESCALATION_CREATED = 'ESCALATION_CREATED';
    case ARCHIVE_APPROVED = 'ARCHIVE_APPROVED';
    case ARCHIVE_BLOCKED = 'ARCHIVE_BLOCKED';
    case SENSITIVE_ACCESS_DENIED = 'SENSITIVE_ACCESS_DENIED';
    case DEMOGRAPHIC_UPDATED = 'DEMOGRAPHIC_UPDATED';
    case DEMOGRAPHIC_WITHDRAWN = 'DEMOGRAPHIC_WITHDRAWN';

    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}
