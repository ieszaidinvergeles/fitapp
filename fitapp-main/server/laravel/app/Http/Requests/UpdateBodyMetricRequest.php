<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBodyMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|nullable|exists:users,id',
            'date' => 'sometimes|required|date',
            'weight_kg' => 'sometimes|required|numeric|min:0|max:999.9',
            'height_cm' => 'sometimes|required|numeric|min:0|max:999.9',
            'body_fat_pct' => 'sometimes|nullable|numeric|min:0|max:100',
            'muscle_mass_pct' => 'sometimes|nullable|numeric|min:0|max:100',
        ];
    }
}