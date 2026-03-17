<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80',
            'type' => 'required|in:physical,online,duo',
            'allow_partner_link' => 'required|boolean',
            'price' => 'required|numeric|min:0|max:9999.99',
        ];
    }
}