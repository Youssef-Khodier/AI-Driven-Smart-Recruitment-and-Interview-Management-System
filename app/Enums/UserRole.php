<?php

namespace App\Enums;

enum UserRole: string
{
    case HR_ADMIN = 'HR_ADMIN';
    case INTERVIEWER = 'INTERVIEWER';
    case CANDIDATE = 'CANDIDATE';
}
