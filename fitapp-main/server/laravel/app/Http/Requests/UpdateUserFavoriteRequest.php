<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserFavoriteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => 'sometimes|required|exists:users,id',
            'entity_type' => 'sometimes|required|in:gym,activity,routine',
            'entity_id' => 'sometimes|required|integer',
        ];
    }
}