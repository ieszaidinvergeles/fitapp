<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'sometimes|nullable|exists:users,id',
            'gym_id' => 'sometimes|nullable|exists:gyms,id',
            'clock_in' => 'sometimes|required|date',
            'clock_out' => 'sometimes|nullable|date|after:clock_in',
            'date' => 'sometimes|required|date',
        ];
    }
}