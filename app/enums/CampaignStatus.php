<?php

namespace App\Enums;

enum CampaignStatus: string
{
    case Draft = 'Draft';
    case Active = 'Active';
    case Closed = 'Closed';
}
