<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStaffAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'staff_id' => 'nullable|exists:users,id',
            'gym_id' => 'nullable|exists:gyms,id',
            'clock_in' => 'required|date',
            'clock_out' => 'nullable|date|after:clock_in',
            'date' => 'required|date',
        ];
    }
}