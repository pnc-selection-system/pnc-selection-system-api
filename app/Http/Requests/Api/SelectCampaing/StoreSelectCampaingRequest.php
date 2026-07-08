<?php

namespace App\Http\Requests\Api\SelectCampaing;

use Illuminate\Foundation\Http\FormRequest;

class StoreSelectCampaingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:100',
            'year'            => 'required|integer|min:1900|max:2099',
            'condidate_total' => 'required|integer|min:0',
            'start_date'      => 'required|date',
            'end_date'        => 'required|date|after_or_equal:start_date',
            'status'          => 'sometimes|in:Draft,Active,Closed',
        ];
    }
}
