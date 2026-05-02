# Gym — Authentication System Implementation

**Date:** 2026-03-27 (Release: `v0.6.0-auth-system`)

# 1. Authentication Conventions

## 1.1. Architecture and Design Principles

The authentication system follows a layered architecture aligned with SOLID principles, particularly **Single Responsibility Principle (SRP)** and **Dependency Inversion Principle (DIP)**. Each layer handles one concern and knows nothing about the internal implementation of the others.

| Layer | Component | Responsibility |
| --- | --- | --- |
| `routing` | `routes/api.php` | Map HTTP endpoints to controller actions |
| `validation` | `Requests/Auth/*.php` | Validate input and reject invalid payloads |
| `control` | `Controllers/Auth/AuthController.php` | Orchestrate authentication flows |
| `persistence` | `Models/User.php` | Represent user identity and relationships |
| `audit` | `Models/Log/AuthLog.php` | Record immutable authentication events |

> Each layer is strictly isolated. Controllers do not validate, models do not orchestrate, and requests do not contain business logic.

## 1.2. Authentication Strategy: Sanctum vs JWT

| Strategy | Description | Status |
| --- | --- | --- |
| `Sanctum` | Laravel-native token system using personal access tokens | **Implemented** |
| `JWT` | Stateless token with embedded claims (self-contained) | Not used |

### Why Sanctum?

1. **Native Laravel integration:** No external dependencies required.
2. **Database-controlled tokens:** Tokens can be revoked instantly server-side, unlike JWT which cannot be invalidated once issued.
3. **Security:** Avoids risks of long-lived stateless tokens that remain valid even after a user logs out or is blocked.
4. **Simplicity:** Token issuance, validation, and revocation are handled entirely by the framework without additional infrastructure.

### Why NOT JWT?

* Tokens cannot be revoked without implementing a token blacklist system — adding infrastructure complexity.
* Long-lived access tokens are a security risk in a gym environment with member blocking requirements.
* Requires additional infrastructure for refresh token rotation that Sanctum handles natively.

> Sanctum is preferred for API-first applications where token revocation and simplicity matter more than full statelessness.

## 1.3. Token Behavior and Lifecycle

| Operation | Implementation |
| --- | --- |
| Token creation | `$user->createToken('api-token')` |
| Token storage | `personal_access_tokens` table |
| Token validation | `auth:sanctum` middleware |
| Token revocation | `$request->user()->currentAccessToken()->delete()` |

> Tokens are hashed before storage, meaning the raw token is never persisted in the database. The only time the plain-text token is available is at the moment of creation, when it is returned in the login response.

## 1.4. Route Protection Rules

### Public routes

* No authentication required.
* Used for onboarding and password recovery flows where the user has no session yet.

### Protected routes

* Require `auth:sanctum` middleware, verified on every request.
* Any request with an invalid, expired, or revoked token receives a `401 Unauthorized` response.
* All business endpoints live exclusively in the protected group.

# 2. User Model and Role System

## 2.1. User Model Structure

**File:** `app/Models/User.php`
**Extends:** `Authenticatable`
**Implements:** `MustVerifyEmail`

### Traits

| Trait | Purpose |
| --- | --- |
| `HasApiTokens` | Enables Sanctum token creation and management |
| `HasFactory` | Testing and seeding |
| `Notifiable` | Email delivery for verification and password reset |

## 2.2. Custom Password Column

The project uses `password_hash` as the column name instead of Laravel's default `password`. This requires the following override in the User model:

```php
public function getAuthPasswordName(): string
{
    return 'password_hash';
}
```

> Without this override, Sanctum and password reset flows would break silently because they internally call `getAuthPassword()` which reads `getAuthPasswordName()` to find the correct column. The rename exists to make it semantically clear in the database that the value is always a hash, never plain text.

## 2.3. Role System

| Role | Capabilities | API Access |
| --- | --- | --- |
| `admin` | Full system access | Yes |
| `manager` | Manage gyms and staff within their assigned gym | Yes |
| `staff` | Operational tasks within their assigned gym | Yes |
| `client` | Booking and usage of the gym | Yes |
| `user_online` | Limited digital-only access (no physical gym features) | Limited |

## 2.4. Role Helper Methods

All role checks are centralized in the `User` model as helper methods. Controllers and Policies call these helpers instead of checking the raw `role` string directly. This avoids repeated string comparisons throughout the codebase and makes it trivial to extend roles in the future.

| Method | Returns | Logic |
| --- | --- | --- |
| `isAdmin()` | `bool` | `$this->role === 'admin'` |
| `isManager()` | `bool` | `$this->role === 'manager'` |
| `isStaff()` | `bool` | `$this->role === 'staff'` |
| `isAdvanced()` | `bool` | `role` is one of `admin`, `manager`, or `staff` |
| `isUser()` | `bool` | `role` is `client` or `user_online` |

> These helpers centralize role logic and prevent magic strings from spreading across controllers, middleware, and policies.

## 2.5. Casting Rules

| Field | Cast | Purpose |
| --- | --- | --- |
| `password_hash` | `hashed` | Automatic bcrypt encryption before saving |
| `email_verified_at` | `datetime` | Carbon object for time-based comparisons |
| `birth_date` | `date` | Carbon object without time component |
| `is_blocked_from_booking` | `boolean` | Strict true/false for conditional logic |
| `cancellation_strikes` | `integer` | Numeric operations for auto-block logic |

## 2.6. Relationships

| Relationship | Type | Target | Foreign Key |
| --- | --- | --- | --- |
| `currentGym()` | `BelongsTo` | `Gym` | `current_gym_id` |
| `membershipPlan()` | `BelongsTo` | `MembershipPlan` | `membership_plan_id` |
| `bodyMetrics()` | `HasMany` | `BodyMetric` | `user_id` |
| `bookings()` | `HasMany` | `Booking` | `user_id` |
| `createdRoutines()` | `HasMany` | `Routine` | `creator_id` |
| `settings()` | `HasOne` | `Setting` | `user_id` |
| `partners()` | `BelongsToMany` | `User` | `user_partners` |

# 3. Routes and Endpoint Structure

## 3.1. Base Route

All authentication endpoints are grouped under `/api/v1/auth`.

## 3.2. Public Routes

| Method | Endpoint | Action | Validation |
| --- | --- | --- | --- |
| POST | `/register` | `register` | `RegisterRequest` |
| POST | `/login` | `login` | `LoginRequest` |
| POST | `/forgot-password` | `forgotPassword` | `ForgotPasswordRequest` |
| POST | `/reset-password` | `resetPassword` | `ResetPasswordRequest` |

## 3.3. Protected Routes

| Method | Endpoint | Action | Validation |
| --- | --- | --- | --- |
| POST | `/logout` | `logout` | — |
| GET | `/me` | `me` | — |
| POST | `/email/resend` | `resendVerification` | `ResendVerificationRequest` |
| GET | `/email/verify/{id}/{hash}` | `verifyEmail` | `signed` middleware |

# 4. Form Request Validation

## 4.1. General Rules

* All requests extend `FormRequest`.
* Each class handles a single responsibility: one class per user-facing operation.
* Controllers receive only validated data via `$request->validated()`.
* Validation is completely decoupled from controllers to enforce SRP and ensure reusable, testable validation logic.

## 4.2. Validation Definitions

### RegisterRequest

| Field | Rules |
| --- | --- |
| `full_name` | `required`, `string`, `max:160` |
| `email` | `required`, `email`, `unique:users`, `max:160` |
| `password` | `required`, `string`, `min:8`, `max:255`, uppercase + digit + special char regex |
| `password_confirmation` | `required`, `same:password` |
| `dni` | `required`, `string`, `size:9`, `unique:users`, Spanish DNI format regex |
| `birth_date` | `required`, `date`, `before:2010-01-01` |

### LoginRequest

| Field | Rules |
| --- | --- |
| `email` | `required`, `email`, `max:160` |
| `password` | `required`, `string` |

### ForgotPasswordRequest

| Field | Rules |
| --- | --- |
| `email` | `required`, `email`, `exists:users`, `max:160` |

> The `exists:users` rule prevents timing attacks that could confirm which emails are registered by always returning the same response regardless of whether the email exists.

### ResetPasswordRequest

| Field | Rules |
| --- | --- |
| `email` | `required`, `email`, `exists:users`, `max:160` |
| `token` | `required`, `string` |
| `password` | `required`, `string`, `min:8`, `max:255` |
| `password_confirmation` | `required`, `same:password` |

# 5. Controller Architecture

## 5.1. AuthController Responsibilities

| Action | Purpose |
| --- | --- |
| `register()` | Create user, initialize settings row, return token |
| `login()` | Verify credentials, issue Sanctum token, log event |
| `logout()` | Revoke current access token |
| `me()` | Return authenticated user's data |
| `forgotPassword()` | Generate and email a reset link |
| `resetPassword()` | Validate token and update `password_hash` |
| `verifyEmail()` | Mark `email_verified_at` via signed URL |
| `resendVerification()` | Re-dispatch verification email |

## 5.2. Flow Design Principles

* Registration wraps user creation and settings initialization in a database transaction. If either fails, neither is committed — this prevents orphaned users without settings.
* No authentication business logic lives in routes or model observers.
* All authentication events (login, logout, failed attempts) are written to `auth_logs` as a side effect of the corresponding controller action.

# 6. Middleware

## 6.1. Role-Based Access

| Middleware | Purpose | HTTP Error on Deny |
| --- | --- | --- |
| `auth:sanctum` | Verify valid token exists | `401` |
| `admin` | Restrict endpoint to `admin` role | `403` |
| `advanced` | Restrict endpoint to `admin`, `manager`, or `staff` roles | `403` |

## 6.2. Middleware Registration

Both custom middleware classes (`AdminMiddleware`, `AdvancedMiddleware`) are registered in `bootstrap/app.php` under short aliases. They use `isAdmin()` and `isAdvanced()` from the User model directly, not hardcoded string comparisons.

# 7. Configuration

## 7.1. Auth Configuration

| Setting | Value | Explanation |
| --- | --- | --- |
| `guard` | `api` | Route group guard for all API calls |
| `driver` | `sanctum` | Token provider |
| `provider` | `users` | Eloquent model used for identity resolution |
| `reset expiry` | `60 minutes` | Password reset links expire after 60 minutes |
| `verification expiry` | `60 minutes` | Email verification links expire after 60 minutes |

# 8. AuthLog System

## 8.1. Design Principles

* **Immutable:** No `UPDATE` or `DELETE` operations are ever executed on this table.
* **No foreign keys:** Logs must survive user deletion. A cascade would destroy the audit trail. See `laravel_database_logs_review.md` for the full rationale.
* **Independent lifecycle:** Auth logs exist independently of users. A deleted user's login history remains permanently.

## 8.2. Fields

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `user_id` | `unsignedBigInteger` | Yes | References the user, but without FK — may reference a deleted user |
| `email_attempt` | `string(160)` | No | The email used in the attempt — stored even if the account does not exist |
| `event` | `enum` | No | The type of authentication event |
| `ip_address` | `string(45)` | No | Supports both IPv4 (15 chars) and IPv6 (39 chars) |
| `user_agent` | `text` | Yes | Browser or client string, may be absent on API requests |
| `created_at` | `timestamp` | No | Event timestamp, set automatically |

## 8.3. Event Values

| Event | Description |
| --- | --- |
| `login_ok` | Successful login — correct credentials, token issued |
| `login_failed` | Failed login — incorrect credentials |
| `logout` | User explicitly revoked their token |
| `password_reset_requested` | User requested a reset link |
| `password_reset_ok` | Password was successfully changed via reset |

> Auth logs intentionally avoid foreign keys to prevent data loss when users are deleted. This ensures a complete, legally valid audit trail. See `laravel_database_logs_review.md` for the architectural reasoning.

# 9. Database Tables — Auth Related

## 9.1. users (auth-relevant columns)

| Column | Type | Purpose |
| --- | --- | --- |
| `id` | PK | Unique identifier |
| `email` | string(160) | Login identity — must be unique |
| `password_hash` | string(255) | Bcrypt hash of the password |
| `email_verified_at` | timestamp, nullable | Null means unverified |
| `role` | enum | Authorization level |
| `remember_token` | string(100), nullable | Used by Laravel's built-in remember-me flow |

## 9.2. personal_access_tokens

| Column | Type | Purpose |
| --- | --- | --- |
| `tokenable_id` | FK → users | The user who owns the token |
| `token` | string(64) | Hashed token — the raw value is only returned at creation |
| `abilities` | text, nullable | JSON array of permitted actions |
| `last_used_at` | timestamp, nullable | Tracks last usage for activity detection |
| `expires_at` | timestamp, nullable | Optional expiry, not currently set |

## 9.3. password_reset_tokens

| Column | Type | Purpose |
| --- | --- | --- |
| `email` | string | Composite PK — one active token per email |
| `token` | string | Hashed reset token |
| `created_at` | timestamp | Used to enforce the 60-minute expiry |

# 10. Email Notifications

## 10.1. Email Verification

* Triggered automatically after successful registration.
* Uses a signed URL containing the user ID and a hash of the email.
* The link expires in 60 minutes and is invalidated after first use.
* If not verified, certain protected operations may be restricted (enforced by `MustVerifyEmail`).

## 10.2. Password Reset

* User submits their email to `/forgot-password`.
* A reset token is stored in `password_reset_tokens` and emailed as a signed link.
* The link expires after 60 minutes.
* On successful reset, all `personal_access_tokens` for that user are revoked to force re-login on all devices.

# 11. Security Implementation Summary

## 11.1. Features Applied

| Feature | Status | Notes |
| --- | --- | --- |
| Password hashing | Yes | `hashed` cast via `password_hash` column |
| Email verification | Yes | `MustVerifyEmail`, signed URL |
| Token authentication | Yes | Sanctum personal access tokens |
| IP logging | Yes | Recorded in `auth_logs` |
| Database transactions | Yes | Register flow wrapped in transaction |
| Token revocation on reset | Yes | All tokens cleared on password change |

# 12. Code Comments

* No inline comments (`//`) in migration columns.
* JavaDoc in the top block of each migration class and each controller method.
