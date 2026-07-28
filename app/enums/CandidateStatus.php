<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Register = 'Register';
    case ExamPassed = 'Exam Passed';
    case Assessed = 'Assessed';
    case InterestAssessmentFail = 'Interest Assessment Fail';
    case Investigating = 'Investigating';
    case Shortlisted = 'Shortlisted';
    case InVoting = 'In Voting';
    case Selected = 'Selected';
    case Waitlisted = 'Waitlisted';
    case NotSelected = 'Not Selected';
}
