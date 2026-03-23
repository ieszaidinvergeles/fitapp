<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:80',
            'description' => 'sometimes|nullable|string|max:280',
            'intensity_level' => 'sometimes|required|in:low,medium,high,extreme',
        ];
    }
}