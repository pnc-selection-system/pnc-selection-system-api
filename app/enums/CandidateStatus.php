<?php

namespace App\Enums;

enum CandidateStatus: string
{
    case Register = 'Register';
    case ExamPassed = 'Exam Passed';
    case ExamFail = 'Exam Fail';
    case Absent = 'Absent';
    case InterestAssessmentPassed = 'Interest Assessment Passed';
    case InterestAssessmentFail = 'Interest Assessment Fail';
    case Investigating = 'Investigating';
    case Investigated = 'Investigated';
    case Shortlisted = 'Shortlisted';
    case InVoting = 'In Voting';
    case Selected = 'Selected';
    case Waitlisted = 'Waitlisted';
    case NotSelected = 'Not Selected';
}
