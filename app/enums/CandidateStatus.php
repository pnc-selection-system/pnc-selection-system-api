<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Register = 'Register';
    case ExamPassed = 'Exam Passed';
    case Assessed = 'Assessed';
    case Investigating = 'Investigating';
}
