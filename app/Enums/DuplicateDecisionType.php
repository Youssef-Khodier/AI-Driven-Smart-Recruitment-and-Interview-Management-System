<?php

namespace App\Enums;

enum DuplicateDecisionType: string {
    case MERGE = 'MERGE';
    case IGNORE = 'IGNORE';
    case DEFER = 'DEFER';
}
