<?php

namespace App\Http\Requests\Campaign;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:150|unique:campaigns,name',
            'year' => 'required|integer|min:1900|max:' . (int) date('Y') + 10,
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after:start_date',
            'status' => ['required', Rule::in(['draft', 'active', 'closed'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Campaign name is required.',
            'name.unique' => 'A campaign with this name already exists.',
            'year.required' => 'Year is required.',
            'year.integer' => 'Year must be a valid integer.',
            'year.min' => 'Year must not be before 1900.',
            'year.max' => 'Year cannot be more than 10 years in the future.',
            'start_date.required' => 'Start date is required.',
            'start_date.date' => 'Start date must be a valid date.',
            'start_date.date_format' => 'Start date format must be YYYY-MM-DD.',
            'end_date.required' => 'End date is required.',
            'end_date.date' => 'End date must be a valid date.',
            'end_date.date_format' => 'End date format must be YYYY-MM-DD.',
            'end_date.after' => 'End date must be after start date.',
            'status.required' => 'Campaign status is required.',
            'status.in' => 'Invalid campaign status provided.',
        ];
    }
}
