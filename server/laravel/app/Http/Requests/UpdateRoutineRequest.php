<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRoutineRequest extends FormRequest
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
            'creator_id' => 'sometimes|nullable|exists:users,id',
            'difficulty_level' => 'sometimes|required|in:beginner,intermediate,advanced,expert',
            'estimated_duration_min' => 'sometimes|required|integer|min:1',
            'associated_diet_plan_id' => 'sometimes|nullable|exists:diet_plans,id',
        ];
    }
}