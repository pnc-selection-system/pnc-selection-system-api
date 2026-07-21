<?php

namespace App\Http\Requests\Api\Candidate;

use Illuminate\Foundation\Http\FormRequest;

class ImportCandidateConfirmRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'import_file_id'   => 'required|integer|exists:import_files,id',
            'column_mapping'   => 'required|array|min:1',
            'column_mapping.*' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'import_file_id.required' => 'Import file ID is required.',
            'import_file_id.exists'   => 'Import file not found.',
            'column_mapping.required' => 'Column mapping is required.',
            'column_mapping.*.required' => 'Each mapped column must have a value.',
        ];
    }
}
