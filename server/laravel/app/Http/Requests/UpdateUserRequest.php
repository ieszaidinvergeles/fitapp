<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('id') ?? $this->route('user')?->id;

        return [
            'username' => 'sometimes|required|string|max:20',
            'email' => 'sometimes|required|email|unique:users,email,' . $userId,
            'password_hash' => 'sometimes|nullable|string|min:8',
            'role' => 'sometimes|required|in:admin,manager,assistant,staff,client,user_online',
            'full_name' => 'sometimes|nullable|string|max:160',
            'dni' => 'sometimes|required|string|size:9',
            'birth_date' => 'sometimes|required|date',
            'profile_photo_url' => 'sometimes|nullable|url|max:500',
            'current_gym_id' => 'sometimes|nullable|exists:gyms,id',
            'membership_plan_id' => 'sometimes|nullable|exists:membership_plans,id',
            'membership_status' => 'sometimes|required|in:active,paused,expired',
            'cancellation_strikes' => 'sometimes|required|integer|min:0',
            'is_blocked_from_booking' => 'sometimes|required|boolean',
        ];
    }
}
