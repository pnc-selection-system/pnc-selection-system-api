<?php

namespace App\Enums;

enum ReportType: string
{
    case FinalSelectedList = 'final-selected-list';
    case ExamResults = 'exam-results';
    case InvestigationSummary = 'investigation-summary';
    case VotingRecord = 'voting-record';

    public function label(): string
    {
        return match ($this) {
            self::FinalSelectedList   => 'Final selected list',
            self::ExamResults         => 'Exam results',
            self::InvestigationSummary => 'Investigation summary',
            self::VotingRecord        => 'Voting record',
        };
    }
}
