<?php

namespace App\Enums;

enum Recommendation: string
{
    case Approve = 'approve';
    case Reject = 'reject';
    case Review = 'review';
}
