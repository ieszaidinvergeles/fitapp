<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserMealScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'date' => 'sometimes|required|date',
            'meal_type' => 'sometimes|required|in:breakfast,lunch,dinner,snack,pre_workout,post_workout',
            'recipe_id' => 'sometimes|nullable|exists:recipes,id',
            'is_consumed' => 'sometimes|required|boolean',
        ];
    }
}