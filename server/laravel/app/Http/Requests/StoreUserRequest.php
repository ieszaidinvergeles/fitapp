<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:20|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password_hash' => 'required|string|min:8',
            'role' => 'required|in:admin,manager,assistant,staff,client,user_online',
            'full_name' => 'nullable|string|max:160',
            'dni' => 'required|string|size:9',
            'birth_date' => 'required|date',
            'profile_photo_url' => 'nullable|url|max:500',
            'current_gym_id' => 'nullable|exists:gyms,id',
            'membership_plan_id' => 'nullable|exists:membership_plans,id',
            'membership_status' => 'nullable|in:active,paused,expired',
            'cancellation_strikes' => 'nullable|integer|min:0',
            'is_blocked_from_booking' => 'nullable|boolean',
        ];
    }
}
