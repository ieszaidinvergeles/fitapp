<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id|unique:settings,user_id',
            'share_workout_stats' => 'required|boolean',
            'share_body_metrics' => 'required|boolean',
            'share_attendance' => 'required|boolean',
            'theme_preference' => 'required|boolean',
            'language_preference' => 'required|in:es,en',
        ];
    }
}