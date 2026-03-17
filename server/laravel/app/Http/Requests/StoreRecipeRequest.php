<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRecipeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80',
            'description' => 'required|string',
            'ingredients' => 'required|string',
            'preparation_steps' => 'required|string',
            'calories' => 'required|integer|min:0',
            'macros_json' => 'nullable|json',
            'type' => 'required|in:breakfast,lunch,dinner,snack,pre_workout,post_workout',
            'image_url' => 'nullable|url',
        ];
    }
}