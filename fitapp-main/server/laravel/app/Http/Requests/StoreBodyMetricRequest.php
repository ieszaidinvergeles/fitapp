<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBodyMetricRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|exists:users,id',
            'date' => 'required|date',
            'weight_kg' => 'required|numeric|min:0|max:999.9',
            'height_cm' => 'required|numeric|min:0|max:999.9',
            'body_fat_pct' => 'nullable|numeric|min:0|max:100',
            'muscle_mass_pct' => 'nullable|numeric|min:0|max:100',
        ];
    }
}