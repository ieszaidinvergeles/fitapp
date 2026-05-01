<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the login request payload.
 *
 * SRP: Solely responsible for login input validation rules and messages.
 * ISP: Exposes only the rules required for authentication, not registration.
 */
class LoginRequest extends FormRequest
{
    /**
     * Determines if the user is authorised to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Returns the human-readable attribute names used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'email'    => 'email address',
            'password' => 'password',
        ];
    }

    /**
     * Returns custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.required'    => 'The :attribute is required.',
            'email.email'       => 'The :attribute must be a valid email address.',
            'password.required' => 'The :attribute is required.',
            'password.string'   => 'The :attribute must be a string.',
        ];
    }

    /**
     * Returns the validation rules for the login request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ];
    }
}
