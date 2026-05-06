<?php

namespace App\Enums;

enum SyncStatus: string
{
    case QUEUED = 'QUEUED';
    case PUBLISHED = 'PUBLISHED';
    case UNPUBLISHED = 'UNPUBLISHED';
    case FAILED = 'FAILED';
}
