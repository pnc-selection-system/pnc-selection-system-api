<?php

namespace App\Enums;

enum VoteDecision: string
{
    case Approve = 'Approve';
    case Reject = 'Reject';
    case Abstain = 'Abstain';
}
