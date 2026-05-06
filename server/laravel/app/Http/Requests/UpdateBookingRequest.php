<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'sometimes|required|exists:classes,id',
            'user_id' => 'sometimes|required|exists:users,id',
            'status' => 'sometimes|required|in:active,cancelled,attended,no_show',
        ];
    }
}