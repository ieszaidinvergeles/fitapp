<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates the password reset request payload.
 *
 * SRP: Solely responsible for validating the token, email and new password fields.
 */
class ResetPasswordRequest extends FormRequest
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
            'token'    => 'reset token',
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
            'token.required'    => 'The :attribute is required.',
            'email.required'    => 'The :attribute is required.',
            'email.email'       => 'The :attribute must be a valid email address.',
            'password.required' => 'The :attribute is required.',
            'password.confirmed'=> 'The :attribute confirmation does not match.',
        ];
    }

    /**
     * Returns the validation rules for the password reset request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'token'    => ['required', 'string'],
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
        ];
    }
}
