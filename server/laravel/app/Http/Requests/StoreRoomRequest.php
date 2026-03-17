<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'gym_id' => 'required|exists:gyms,id',
            'name' => 'required|string|max:80',
            'capacity' => 'required|integer|min:1',
        ];
    }
}