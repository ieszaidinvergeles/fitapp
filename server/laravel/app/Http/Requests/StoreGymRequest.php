<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGymRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'            => 'required|string|max:80',
            'manager_id'      => 'nullable|exists:users,id',
            'address'         => 'required|string|max:160',
            'city'            => 'required|string|max:80',
            'location_coords' => 'nullable|string|max:100',
            'phone'           => 'required|string|max:20',
            'logo'            => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ];
    }
}