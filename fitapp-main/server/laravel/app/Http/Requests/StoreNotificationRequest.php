<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sender_id' => 'nullable|exists:users,id',
            'title' => 'required|string|max:160',
            'body' => 'nullable|string',
            'target_audience' => 'nullable|in:global,staff_only,specific_gym,specific_user',
            'related_gym_id' => 'nullable|exists:gyms,id',
        ];
    }
}