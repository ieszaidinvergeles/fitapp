<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGymClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_id' => 'required|exists:gyms,id',
            'activity_id' => 'nullable|exists:activities,id',
            'instructor_id' => 'nullable|exists:users,id',
            'room_id' => 'nullable|exists:rooms,id',
            'start_time' => 'required|date',
            'end_time' => 'nullable|date|after:start_time',
            'capacity_limit' => 'nullable|integer|min:1',
            'is_cancelled' => 'required|boolean',
        ];
    }
}