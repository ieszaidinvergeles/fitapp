<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/**
 * Validates the user registration request payload.
 *
 * SRP: Solely responsible for registration input validation rules and messages.
 * ISP: Exposes only the rules required for user creation.
 */
class RegisterRequest extends FormRequest
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
            'username'   => 'username',
            'email'      => 'email address',
            'password'   => 'password',
            'full_name'  => 'full name',
            'dni'        => 'DNI',
            'birth_date' => 'date of birth',
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
            'username.required'   => 'The :attribute is required.',
            'username.max'        => 'The :attribute may not exceed :max characters.',
            'username.unique'     => 'This :attribute is already taken.',
            'email.required'      => 'The :attribute is required.',
            'email.email'         => 'The :attribute must be a valid email address.',
            'email.unique'        => 'This :attribute is already registered.',
            'password.required'   => 'The :attribute is required.',
            'password.confirmed'  => 'The :attribute confirmation does not match.',
            'dni.required'        => 'The :attribute is required.',
            'dni.max'             => 'The :attribute may not exceed :max characters.',
            'birth_date.required' => 'The :attribute is required.',
            'birth_date.date'     => 'The :attribute must be a valid date.',
            'birth_date.before'   => 'You must be at least 16 years old to register.',
        ];
    }

    /**
     * Returns the validation rules for the registration request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'username'   => ['required', 'string', 'max:20', 'unique:users,username'],
            'email'      => ['required', 'email', 'max:160', 'unique:users,email'],
            'password'   => ['required', 'string', 'confirmed', Password::min(8)->letters()->numbers()],
            'full_name'  => ['nullable', 'string', 'max:160'],
            'dni'        => ['required', 'string', 'max:9'],
            'birth_date' => ['required', 'date', 'before:' . now()->subYears(16)->toDateString()],
        ];
    }
}
