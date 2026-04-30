<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMembershipPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'sometimes|required|string|max:80',
            'type'               => 'sometimes|required|in:physical,online,duo',
            'allow_partner_link' => 'sometimes|required|boolean',
            'price'              => 'sometimes|required|numeric|min:0|max:9999.99',
            'image'              => 'sometimes|nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ];
    }
}