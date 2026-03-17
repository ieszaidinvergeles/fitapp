<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExerciseRequest extends FormRequest
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
            'image_url' => 'nullable|url|max:500',
            'video_url' => 'nullable|url|max:500',
            'target_muscle_group' => 'nullable|in:chest,upper_back,lower_back,shoulders,biceps,triceps,forearms,core,obliques,quadriceps,hamstrings,glutes,calves,hip_flexors,adductors,abductors,traps,lats,neck,full_body',
        ];
    }
}