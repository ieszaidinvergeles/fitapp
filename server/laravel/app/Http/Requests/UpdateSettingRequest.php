<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'share_workout_stats' => 'sometimes|required|boolean',
            'share_body_metrics' => 'sometimes|required|boolean',
            'share_attendance' => 'sometimes|required|boolean',
            'theme_preference' => 'sometimes|required|boolean',
            'language_preference' => 'sometimes|required|in:es,en',
        ];
    }
}