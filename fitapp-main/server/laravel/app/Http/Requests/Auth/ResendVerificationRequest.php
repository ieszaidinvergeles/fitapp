<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the resend email verification request payload.
 *
 * SRP: Solely responsible for validating that the request comes from an
 *      authenticated user — no body fields are required.
 */
class ResendVerificationRequest extends FormRequest
{
    /**
     * Determines if the user is authorised to make this request.
     * Only authenticated users may request a new verification email.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Returns the human-readable attribute names used in validation messages.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Returns custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [];
    }

    /**
     * Returns the validation rules — no body fields required.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }
}
