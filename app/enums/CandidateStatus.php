<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Pending = 'Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case Withdrawn = 'Withdrawn';
    case Held = 'Held';
    case Selected = 'Selected';
}
