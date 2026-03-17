<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:80',
            'description' => 'sometimes|required|string',
            'ingredients' => 'sometimes|required|string',
            'preparation_steps' => 'sometimes|required|string',
            'calories' => 'sometimes|required|integer|min:0',
            'macros_json' => 'sometimes|nullable|json',
            'type' => 'sometimes|required|in:breakfast,lunch,dinner,snack,pre_workout,post_workout',
            'image_url' => 'sometimes|nullable|url',
        ];
    }
}