<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Processing = 'processing';
    case Ready = 'ready';
    case Failed = 'failed';
}
