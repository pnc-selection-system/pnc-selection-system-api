<?php

namespace App\Enums;

enum InvestigationStatus: string
{
    case Assigned = 'Assigned';
    case InProgress = 'In Progress';
    case Submitted = 'Submitted';
    case Reviewed = 'Reviewed';
}
