<?php

namespace App\Enums;

enum JobRequisitionStatus: string
{
    case DRAFT = 'Draft';
    case PENDING_APPROVAL = 'Pending Approval';
    case APPROVED = 'Approved';
    case OPEN = 'Open';
    case CLOSED = 'Closed';
}
