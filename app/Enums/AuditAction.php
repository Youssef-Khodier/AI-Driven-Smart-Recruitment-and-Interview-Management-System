<?php

namespace App\Enums;

enum AuditAction: string
{
    case USER_CREATED = 'USER_CREATED';
    case ROLE_CHANGED = 'ROLE_CHANGED';
    case STATUS_CHANGED = 'STATUS_CHANGED';
}
