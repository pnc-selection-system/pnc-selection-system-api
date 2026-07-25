<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class ImportCandidateUploadRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'file'        => 'required|file|mimes:csv,txt,xlsx,xls|max:51200',
            'campaign_id' => 'required|integer|exists:selection_campaigns,id',
            'province_id' => 'required|integer|exists:provinces,id',
            'ngo_id'      => 'nullable|integer|exists:ngo_partners,id',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required'     => 'Please upload a file.',
            'file.mimes'        => 'Only CSV and Excel (.xlsx, .xls) files are allowed.',
            'file.max'          => 'File size must not exceed 50MB.',
            'campaign_id.required' => 'Campaign is required.',
            'campaign_id.exists'   => 'Selected campaign does not exist.',
            'province_id.required' => 'Province is required.',
            'province_id.exists'   => 'Selected province does not exist.',
            'ngo_id.exists'        => 'Selected NGO does not exist.',
        ];
    }
}
