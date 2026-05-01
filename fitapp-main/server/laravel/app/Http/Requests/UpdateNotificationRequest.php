<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_id' => 'sometimes|nullable|exists:users,id',
            'title' => 'sometimes|required|string|max:160',
            'body' => 'sometimes|nullable|string',
            'target_audience' => 'sometimes|nullable|in:global,staff_only,specific_gym,specific_user',
            'related_gym_id' => 'sometimes|nullable|exists:gyms,id',
        ];
    }
}