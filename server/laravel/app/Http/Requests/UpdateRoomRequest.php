<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_id'   => 'sometimes|required|exists:gyms,id',
            'name'     => 'sometimes|required|string|max:80',
            'capacity' => 'sometimes|required|integer|min:1',
            'image'    => 'sometimes|nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ];
    }
}