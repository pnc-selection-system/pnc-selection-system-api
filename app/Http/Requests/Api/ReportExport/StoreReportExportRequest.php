<?php

namespace App\Http\Requests\Api\ReportExport;

use App\Enums\ReportType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreReportExportRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'type'        => ['required', Rule::enum(ReportType::class)],
            'export_type' => 'required|string|in:PDF,Excel',
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'report_name' => 'sometimes|string|max:150',
        ];
    }
}
