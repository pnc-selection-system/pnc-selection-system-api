<?php

namespace App\Services\Report;

use App\Models\InfoSession;
use App\Models\Attendance;
use App\Models\InterestStudent;
use App\Models\Cadidate;
use Illuminate\Support\Facades\DB;

class ReportService
{
    public function getSessionStatistics(array $filters): array
    {
        $query = InfoSession::query();

        if (isset($filters['campaign_id'])) {
            $query->where('campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['province_id'])) {
            $query->where('province_id', $filters['province_id']);
        }

        if (isset($filters['school_id'])) {
            $query->where('school_id', $filters['school_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('session_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('session_date', '<=', $filters['date_to']);
        }

        $totalSessions = $query->count();

        $byProvince = $query->clone()
            ->join('provinces', 'information_sessions.province_id', '=', 'provinces.id')
            ->select('provinces.name', DB::raw('COUNT(*) as count'))
            ->groupBy('provinces.id', 'provinces.name')
            ->get();

        $bySchool = $query->clone()
            ->join('schools', 'information_sessions.school_id', '=', 'schools.id')
            ->select('schools.name', DB::raw('COUNT(*) as count'))
            ->groupBy('schools.id', 'schools.name')
            ->get();

        $byCampaign = $query->clone()
            ->join('selection_campaigns', 'information_sessions.campaign_id', '=', 'selection_campaigns.id')
            ->select('selection_campaigns.name', DB::raw('COUNT(*) as count'))
            ->groupBy('selection_campaigns.id', 'selection_campaigns.name')
            ->get();

        return [
            'total_sessions' => $totalSessions,
            'by_province' => $byProvince,
            'by_school' => $bySchool,
            'by_campaign' => $byCampaign
        ];
    }

    public function getAttendanceStatistics(array $filters): array
    {
        $query = Attendance::query()
            ->join('information_sessions', 'attendances.info_session_id', '=', 'information_sessions.id');

        if (isset($filters['campaign_id'])) {
            $query->where('information_sessions.campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['province_id'])) {
            $query->where('information_sessions.province_id', $filters['province_id']);
        }

        if (isset($filters['school_id'])) {
            $query->where('information_sessions.school_id', $filters['school_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('information_sessions.session_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('information_sessions.session_date', '<=', $filters['date_to']);
        }

        $stats = $query->select(
            DB::raw('SUM(total_students) as total_students'),
            DB::raw('SUM(male_students) as male_students'),
            DB::raw('SUM(female_students) as female_students'),
            DB::raw('SUM(teachers_attended) as teachers_attended')
        )->first();

        $bySchool = $query->clone()
            ->join('schools', 'information_sessions.school_id', '=', 'schools.id')
            ->select('schools.name', DB::raw('SUM(attendances.total_students) as total'))
            ->groupBy('schools.id', 'schools.name')
            ->get();

        return [
            'total_students' => $stats->total_students ?? 0,
            'male_students' => $stats->male_students ?? 0,
            'female_students' => $stats->female_students ?? 0,
            'teachers_attended' => $stats->teachers_attended ?? 0,
            'by_school' => $bySchool
        ];
    }

    public function getConversionStatistics(array $filters): array
    {
        $query = InterestStudent::query()
            ->join('information_sessions', 'interested_students.info_session_id', '=', 'information_sessions.id');

        if (isset($filters['campaign_id'])) {
            $query->where('information_sessions.campaign_id', $filters['campaign_id']);
        }

        if (isset($filters['province_id'])) {
            $query->where('information_sessions.province_id', $filters['province_id']);
        }

        if (isset($filters['school_id'])) {
            $query->where('information_sessions.school_id', $filters['school_id']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('information_sessions.session_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('information_sessions.session_date', '<=', $filters['date_to']);
        }

        $totalInterested = $query->count();
        $converted = $query->clone()->where('status', 'converted')->count();

        $conversionRate = $totalInterested > 0 ? round(($converted / $totalInterested) * 100, 2) : 0;

        return [
            'total_interested' => $totalInterested,
            'total_converted' => $converted,
            'conversion_rate' => $conversionRate
        ];
    }

    public function getDashboardData(array $filters): array
    {
        $sessionStats = $this->getSessionStatistics($filters);
        $attendanceStats = $this->getAttendanceStatistics($filters);
        $conversionStats = $this->getConversionStatistics($filters);

        return [
            'sessions' => $sessionStats,
            'attendance' => $attendanceStats,
            'conversion' => $conversionStats
        ];
    }
}