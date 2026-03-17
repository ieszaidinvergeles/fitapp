<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoutineRequest extends FormRequest
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
            'creator_id' => 'nullable|exists:users,id',
            'difficulty_level' => 'required|in:beginner,intermediate,advanced,expert',
            'estimated_duration_min' => 'required|integer|min:1',
            'associated_diet_plan_id' => 'nullable|exists:diet_plans,id',
        ];
    }
}