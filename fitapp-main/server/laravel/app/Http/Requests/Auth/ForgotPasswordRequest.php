<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the forgot password request payload.
 *
 * SRP: Solely responsible for validating the email field in the password reset flow.
 */
class ForgotPasswordRequest extends FormRequest
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
            'email' => 'email address',
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
            'email.required' => 'The :attribute is required.',
            'email.email'    => 'The :attribute must be a valid email address.',
        ];
    }

    /**
     * Returns the validation rules for the forgot password request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
        ];
    }
}
