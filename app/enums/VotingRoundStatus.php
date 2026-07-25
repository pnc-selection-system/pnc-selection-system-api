<?php

namespace App\Enums;

enum VotingRoundStatus: string
{
    case Scheduled = 'Scheduled';
    case Open = 'Open';
    case Closed = 'Closed';
}
