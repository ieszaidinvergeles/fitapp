<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'class_id' => 'required|exists:classes,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:active,cancelled,attended,no_show',
        ];
    }
}