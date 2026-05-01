<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGymClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_id' => 'sometimes|required|exists:gyms,id',
            'activity_id' => 'sometimes|nullable|exists:activities,id',
            'instructor_id' => 'sometimes|nullable|exists:users,id',
            'room_id' => 'sometimes|nullable|exists:rooms,id',
            'start_time' => 'sometimes|required|date',
            'end_time' => 'sometimes|nullable|date|after:start_time',
            'capacity_limit' => 'sometimes|nullable|integer|min:1',
            'is_cancelled' => 'sometimes|required|boolean',
        ];
    }
}