# Gym — Authentication System Implementation v2

This document describes the standardized authentication architecture, design decisions, request validation, middleware behavior, route structure, and audit logging system used in the Gym backend.

# 1. Authentication Conventions

## 1.1. Architecture and Design Principles

The authentication system follows a layered architecture aligned with SOLID principles, particularly **Single Responsibility Principle (SRP)** and **Dependency Inversion Principle (DIP)**.

| Layer         | Component                             | Responsibility                             |
| ------------- | ------------------------------------- | ------------------------------------------ |
| `routing`     | `routes/api.php`                      | Map HTTP endpoints to controller actions   |
| `validation`  | `Requests/Auth/*.php`                 | Validate input and reject invalid payloads |
| `control`     | `Controllers/Auth/AuthController.php` | Orchestrate authentication flows           |
| `persistence` | `Models/User.php`                     | Represent user identity and relationships  |
| `audit`       | `Models/Log/AuthLog.php`              | Record immutable authentication events     |

> Each layer is strictly isolated. Controllers do not validate, models do not orchestrate, and requests do not contain business logic.

---

## 1.2. Authentication Strategy: Sanctum vs JWT

| Strategy  | Description                                              | Status          |
| --------- | -------------------------------------------------------- | --------------- |
| `Sanctum` | Laravel-native token system using personal access tokens | **Implemented** |
| `JWT`     | Stateless token with embedded claims (self-contained)    | Not used        |

### Why Sanctum?

1. **Native Laravel integration:** No external dependencies required.
2. **Database-controlled tokens:** Tokens can be revoked instantly (unlike JWT).
3. **Security:** Avoids risks of long-lived stateless tokens.
4. **Simplicity:** Easier to implement and maintain than JWT.

### Why NOT JWT?

* Tokens cannot be revoked without blacklist systems.
* Adds unnecessary complexity for a monolithic backend.
* Requires additional infrastructure (refresh tokens, rotation, etc.).

> Sanctum is preferred for API-first applications where token revocation and simplicity are more important than full statelessness.

---

## 1.3. Token Behavior and Lifecycle

| Operation        | Implementation                                     |
| ---------------- | -------------------------------------------------- |
| Token creation   | `$user->createToken('api-token')`                  |
| Token storage    | `personal_access_tokens` table                     |
| Token validation | `auth:api` middleware                              |
| Token revocation | `$request->user()->currentAccessToken()->delete()` |

> Tokens are hashed before storage, meaning the raw token is never persisted in the database.

---

## 1.4. Route Protection Rules

### Public routes

* No authentication required
* Used for onboarding and recovery flows

### Protected routes

* Require `auth:api` middleware
* Require valid Sanctum token
* Reject unauthorized requests with `401`

---

# 2. User Model and Role System

## 2.1. User Model Structure

**File:** `app/Models/User.php`
**Extends:** `Authenticatable`
**Implements:** `MustVerifyEmail`

### Traits

| Trait          | Purpose                                       |
| -------------- | --------------------------------------------- |
| `HasApiTokens` | Enables token creation and management         |
| `HasFactory`   | Testing and seeding                           |
| `Notifiable`   | Email delivery (verification, reset password) |

---

## 2.2. Custom Password Column

```php
public function getAuthPasswordName(): string
{
    return 'password_hash';
}
```

> Laravel expects a `password` column by default. This override ensures consistency with the database schema and enforces semantic clarity that the value is always hashed.

---

## 2.3. Role System

| Role          | Capabilities                | API Access |
| ------------- | --------------------------- | ---------- |
| `admin`       | Full system access          | Yes        |
| `manager`     | Manage gyms and staff       | Yes        |
| `staff`       | Operational tasks           | Yes        |
| `client`      | Booking and usage           | Yes        |
| `user_online` | Limited digital-only access | Limited    |

---

## 2.4. Role Helper Methods

| Method         | Returns | Logic                                                |
| -------------- | ------- | ---------------------------------------------------- |
| `isAdmin()`    | `bool`  | `$this->role === 'admin'`                            |
| `isManager()`  | `bool`  | `$this->role === 'manager'`                          |
| `isStaff()`    | `bool`  | `$this->role === 'staff'`                            |
| `isAdvanced()` | `bool`  | `in_array($this->role, ['admin','manager','staff'])` |
| `isUser()`     | `bool`  | `in_array($this->role, ['client','user_online'])`    |

> These helpers centralize role logic and avoid duplication across controllers and middleware.

---

## 2.5. Casting Rules

| Field                     | Cast       | Purpose                 |
| ------------------------- | ---------- | ----------------------- |
| `password_hash`           | `hashed`   | Automatic encryption    |
| `email_verified_at`       | `datetime` | Carbon object           |
| `birth_date`              | `date`     | Carbon object (no time) |
| `is_blocked_from_booking` | `boolean`  | Logical validation      |
| `cancellation_strikes`    | `integer`  | Numeric operations      |

---

## 2.6. Relationships

| Relationship        | Type            | Target           | Foreign Key          |
| ------------------- | --------------- | ---------------- | -------------------- |
| `currentGym()`      | `BelongsTo`     | `Gym`            | `current_gym_id`     |
| `membershipPlan()`  | `BelongsTo`     | `MembershipPlan` | `membership_plan_id` |
| `bodyMetrics()`     | `HasMany`       | `BodyMetric`     | `user_id`            |
| `bookings()`        | `HasMany`       | `Booking`        | `user_id`            |
| `createdRoutines()` | `HasMany`       | `Routine`        | `creator_id`         |
| `settings()`        | `HasOne`        | `Setting`        | `user_id`            |
| `partners()`        | `BelongsToMany` | `User`           | `user_partners`      |

---

# 3. Routes and Endpoint Structure

## 3.1. Base Route

`/v1/auth`

---

## 3.2. Public Routes

| Method | Endpoint           | Action           | Validation              |
| ------ | ------------------ | ---------------- | ----------------------- |
| POST   | `/register`        | `register`       | `RegisterRequest`       |
| POST   | `/login`           | `login`          | `LoginRequest`          |
| POST   | `/forgot-password` | `forgotPassword` | `ForgotPasswordRequest` |
| POST   | `/reset-password`  | `resetPassword`  | `ResetPasswordRequest`  |

---

## 3.3. Protected Routes

| Method | Endpoint                    | Action               | Validation                  |
| ------ | --------------------------- | -------------------- | --------------------------- |
| POST   | `/logout`                   | `logout`             | —                           |
| GET    | `/me`                       | `me`                 | —                           |
| POST   | `/email/resend`             | `resendVerification` | `ResendVerificationRequest` |
| GET    | `/email/verify/{id}/{hash}` | `verifyEmail`        | `signed` middleware         |

---

# 4. Form Request Validation

## 4.1. General Rules

* All requests extend `FormRequest`
* Each class handles a single responsibility
* Controllers receive only validated data

---

## 4.2. Validation Definitions

### RegisterRequest

| Field                   | Rules     |                |                    |              |                                               |
| ----------------------- | --------- | -------------- | ------------------ | ------------ | --------------------------------------------- |
| `username`              | `required | string         | unique:users       | max:80       | regex:/^[a-zA-Z0-9_-.]+$/`                    |
| `email`                 | `required | email          | unique:users       | max:160`     |                                               |
| `password`              | `required | string         | min:8              | max:255      | regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[!@#$%])/` |
| `password_confirmation` | `required | same:password` |                    |              |                                               |
| `full_name`             | `required | string         | max:160`           |              |                                               |
| `dni`                   | `required | string         | size:9             | unique:users | regex:/^[0-9]{8}[A-Za-z]$/`                   |
| `birth_date`            | `required | date           | before:2010-01-01` |              |                                               |

### LoginRequest

| Field      | Rules     |         |          |
| ---------- | --------- | ------- | -------- |
| `email`    | `required | email   | max:160` |
| `password` | `required | string` |          |

### ForgotPasswordRequest

| Field   | Rules     |       |              |          |
| ------- | --------- | ----- | ------------ | -------- |
| `email` | `required | email | exists:users | max:160` |

### ResetPasswordRequest

| Field                   | Rules     |                |              |          |
| ----------------------- | --------- | -------------- | ------------ | -------- |
| `email`                 | `required | email          | exists:users | max:160` |
| `token`                 | `required | string`        |              |          |
| `password`              | `required | string         | min:8        | max:255` |
| `password_confirmation` | `required | same:password` |              |          |

---

## 4.3. Validation Design Decision

> Validation is completely decoupled from controllers to enforce SRP and ensure reusable, testable validation logic.

---

# 5. Controller Architecture

## 5.1. AuthController Responsibilities

| Action                 | Purpose                             |
| ---------------------- | ----------------------------------- |
| `register()`           | Create user and initialize settings |
| `login()`              | Authenticate and issue token        |
| `logout()`             | Revoke token                        |
| `me()`                 | Return authenticated user           |
| `forgotPassword()`     | Send reset link                     |
| `resetPassword()`      | Update password                     |
| `verifyEmail()`        | Mark email verified                 |
| `resendVerification()` | Resend email                        |

---

## 5.2. Flow Design Principles

* All flows are atomic where needed (transactions)
* No business logic leaks into routes or models
* Logging is separated into audit system

---

# 6. Middleware

## 6.1. Role-Based Access

| Middleware | Purpose                         |
| ---------- | ------------------------------- |
| `admin`    | Restrict to admin users         |
| `advanced` | Restrict to admin/manager/staff |

---

## 6.2. Behavior

* Returns `403` if role requirements are not met
* Uses helper methods from User model

---

# 7. Configuration

## 7.1. Auth Configuration

| Setting               | Value     |
| --------------------- | --------- |
| `guard`               | `api`     |
| `driver`              | `sanctum` |
| `provider`            | `users`   |
| `reset expiry`        | `60`      |
| `verification expiry` | `60`      |

---

# 8. AuthLog System

## 8.1. Design Principles

* Immutable (no updates or deletes)
* No foreign keys (historical integrity)
* Independent from user lifecycle

---

## 8.2. Fields

| Field           | Type                 | Nullable |
| --------------- | -------------------- | -------- |
| `user_id`       | `unsignedBigInteger` | Yes      |
| `email_attempt` | `string(160)`        | No       |
| `event`         | `enum`               | No       |
| `ip_address`    | `string(45)`         | No       |
| `user_agent`    | `text`               | Yes      |
| `created_at`    | `timestamp`          | No       |

---

## 8.3. Event Values

| Event                      | Description      |
| -------------------------- | ---------------- |
| `login_ok`                 | Successful login |
| `login_failed`             | Failed login     |
| `logout`                   | Logout           |
| `password_reset_requested` | Reset requested  |
| `password_reset_ok`        | Reset completed  |

---

## 8.4. Design Decision

> Auth logs intentionally avoid foreign keys to prevent data loss when users are deleted. This ensures a complete audit trail.

---

# 9. Database Tables — Auth Related

## 9.1. users

| Column              | Type        | Purpose         |
| ------------------- | ----------- | --------------- |
| `id`                | PK          | Identifier      |
| `username`          | string(80)  | Login           |
| `email`             | string(160) | Identity        |
| `password_hash`     | string(255) | Hashed password |
| `email_verified_at` | timestamp   | Verification    |
| `role`              | enum        | Authorization   |

---

## 9.2. personal_access_tokens

| Column         | Type   | Purpose        |
| -------------- | ------ | -------------- |
| `tokenable_id` | FK     | User reference |
| `token`        | string | Hashed token   |
| `abilities`    | text   | Permissions    |

---

## 9.3. password_reset_tokens

| Column  | Type   | Purpose     |
| ------- | ------ | ----------- |
| `email` | string | Identifier  |
| `token` | string | Reset token |

---

# 10. Notifications

## 10.1. Email Verification

* Triggered after registration
* Uses signed URL
* Expires in 60 minutes

---

# 11. Security Implementation Summary

## 11.1. Features Applied

| Feature            | Status |
| ------------------ | ------ |
| Password hashing   | Yes    |
| Email verification | Yes    |
| Token auth         | Yes    |
| IP logging         | Yes    |
| Transactions       | Yes    |

---

# 12. Code Comments

* No inline comments (`//`) in columns
* JavaDoc in the top block of each migration class
