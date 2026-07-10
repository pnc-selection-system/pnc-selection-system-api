<?php

namespace App\Enums;

enum VotingMethod: string
{
    case Majority = 'Majority';
    case Weighted = 'Weighted';
}
