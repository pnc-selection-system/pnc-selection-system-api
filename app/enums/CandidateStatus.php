<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Register = 'Register';
    case ExamPassed = 'Exam Passed';
    case PassInterest = 'Pass Interest';
    case FailInterest = 'Fail Interest';
    case Investigating = 'Investigating';
}
