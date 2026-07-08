<?php

namespace App\Http\Requests\Api\SelectCampaing;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSelectCampaingRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'            => 'sometimes|required|string|max:100',
            'year'            => 'sometimes|required|integer|min:1900|max:2099',
            'condidate_total' => 'sometimes|required|integer|min:0',
            'start_date'      => 'sometimes|required|date',
            'end_date'        => 'sometimes|required|date|after_or_equal:start_date',
            'status'          => 'sometimes|in:Draft,Active,Closed',
        ];
    }
}
