<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'sometimes|required|string|max:80',
            'manager_id'      => 'sometimes|nullable|exists:users,id',
            'address'         => 'sometimes|required|string|max:160',
            'city'            => 'sometimes|required|string|max:80',
            'location_coords' => 'sometimes|nullable|string|max:100',
            'phone'           => 'sometimes|required|string|max:20',
            'logo'            => 'sometimes|nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ];
    }
}