# Gym — API Request Validators

**Date:** 2026-03-23 (Release: `v0.5.0-validators`)

# 1. Auth Validators

## 1.1. RegisterRequest

| Field                   | Rules |
| ----------------------- | ----- |
| `username`              | `required`, `string`, `unique:users`, `max:80`, `regex:/^[a-zA-Z0-9_-.]+$/` |
| `email`                 | `required`, `email`, `unique:users`, `max:160` |
| `password`              | `required`, `string`, `min:8`, `max:255`, `regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%])/` |
| `password_confirmation` | `required`, `same:password` |
| `full_name`             | `required`, `string`, `max:160` |
| `dni`                   | `required`, `string`, `size:9`, `unique:users`, `regex:/^[0-9]{8}[A-Za-z]$/` |
| `birth_date`            | `required`, `date`, `before:2010-01-01` |

## 1.2. LoginRequest

| Field      | Rules |
| ---------- | ----- |
| `email`    | `required`, `email`, `max:160` |
| `password` | `required`, `string` |

## 1.3. ForgotPasswordRequest

| Field   | Rules |
| ------- | ----- |
| `email` | `required`, `email`, `exists:users`, `max:160` |

## 1.4. ResetPasswordRequest

| Field                   | Rules |
| ----------------------- | ----- |
| `email`                 | `required`, `email`, `exists:users`, `max:160` |
| `token`                 | `required`, `string` |
| `password`              | `required`, `string`, `min:8`, `max:255` |
| `password_confirmation` | `required`, `same:password` |

# 2. General Conventions

* All requests extend `FormRequest`.
* Each class handles a single responsibility.
* Controllers receive only validated data.
* Validation is completely decoupled from controllers to enforce SRP and ensure reusable, testable validation logic.
