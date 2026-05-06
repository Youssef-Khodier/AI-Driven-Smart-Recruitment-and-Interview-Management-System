<?php

namespace App\Enums;

enum DuplicateConfidence: string {
    case HIGH = 'HIGH';
    case MEDIUM = 'MEDIUM';
    case LOW = 'LOW';
}
