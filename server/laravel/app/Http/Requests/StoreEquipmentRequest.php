<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'               => 'required|string|max:80',
            'description'        => 'nullable|string|max:280',
            'is_home_accessible' => 'required|boolean',
            'image'              => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
        ];
    }
}