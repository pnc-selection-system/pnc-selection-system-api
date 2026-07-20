<?php

namespace App\Http\Requests\Api\SelectCampaing;

use App\Enums\CampaignStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSelectCampaingRequest extends FormRequest
{
    public function rules(): array
    {
        $rules = [
            'name'            => 'sometimes|required|string|max:100',
            'year'            => 'sometimes|required|integer|min:1900|max:2099',
            'condidate_total' => 'sometimes|required|integer|min:0',
            'start_date'      => 'sometimes|required|date',
            'status'          => ['sometimes', Rule::enum(CampaignStatus::class)],
            'province_ids'    => 'sometimes|array',
            'province_ids.*'  => 'required|integer|exists:provinces,id',
        ];

        if ($this->has('start_date') && $this->has('end_date')) {
            $rules['end_date'] = 'sometimes|required|date|after_or_equal:start_date';
        } elseif ($this->has('end_date')) {
            $rules['end_date'] = 'sometimes|required|date';
        }

        return $rules;
    }
}
