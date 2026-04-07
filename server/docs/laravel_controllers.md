# Gym — Controllers, API Architecture and Middleware

**Date:** 2026-04-06 (Release: `v0.8.0-controllers`)

# 1. API Infrastructure and Routing

## 1.1. Unified Routing and Versioning

The backend exposes a **RESTful API** under the `api/v1/` prefix. All 105 routes are consolidated in `routes/api.php` under a single versioned group. The `v1` prefix exists so that a future breaking change can be introduced as `v2` without affecting clients currently consuming `v1`.

> Not versioning the API from the start is one of the most common technical debts in backend development. Adding versioning retroactively requires coordinating changes across every consumer at once. Doing it from the beginning costs nothing.

## 1.2. Route Organization

Routes are organized in nested groups by resource and access level:

| Group | Middleware Applied | Number of Routes |
| --- | --- | --- |
| Auth (`/v1/auth`) | None (public) | 4 |
| Auth protected (`/v1/auth`) | `auth:sanctum` | 4 |
| Open resources | `auth:sanctum` | ~30 (read-only listings) |
| Advanced resources | `auth:sanctum`, `advanced` | ~50 (mutations for staff+) |
| Admin resources | `auth:sanctum`, `admin` | ~21 (admin-only operations) |

## 1.3. Role-Based Access Control (RBAC)

Authorization is handled through custom middleware registered in `bootstrap/app.php`. Each middleware calls the corresponding helper method from the `User` model rather than comparing raw role strings.

| Alias | Middleware Class | Role Access | HTTP on Deny |
| --- | --- | --- | --- |
| `auth:sanctum` | Built-in Laravel Sanctum | Any authenticated user | `401` |
| `advanced` | `AdvancedMiddleware` | `admin`, `manager`, or `staff` | `403` |
| `admin` | `AdminMiddleware` | `admin` only | `403` |

> As of v0.9.0, middleware-level RBAC was complemented by Policy-level authorization. The middleware acts as a coarse filter (wrong role entirely), while Policies enforce fine-grained rules (correct role, but wrong gym or wrong record ownership).

# 2. Controller Architecture

## 2.1. Thin Controller Principle

All 18 controllers follow the **Thin Controller** pattern. A controller is not responsible for understanding business rules — it is only responsible for:

1. Receiving an HTTP request.
2. Triggering authorization via `$this->authorize()`.
3. Delegating database work to Eloquent.
4. Returning a formatted response.

No business logic (calculations, state machines, cross-table validation) lives inside a controller. That logic belongs in the model or a dedicated service class.

## 2.2. Standardized Response Format

Every endpoint returns a JSON body with exactly two top-level keys:

| Key | Type | Purpose |
| --- | --- | --- |
| `result` | `mixed` or `false` | The data payload if successful; `false` if not |
| `message` | `object` | Always contains at least `general` with a human-readable status string |

This structure is consistent across all 105 routes and all error conditions. The frontend never has to check whether a field exists — `result` and `message.general` are always present.

## 2.3. Try-Catch Pattern

Every controller method wraps its logic in a `try-catch` block. The default values of `$result = false` and an error message are set before the try block. If an exception is thrown at any point, the catch replaces the message with the exception text and returns the pre-initialized failure state.

This prevents SQL error messages, stack traces, or internal model exceptions from leaking into the HTTP response under any circumstance.

## 2.4. Pagination

All listing endpoints (`index`) return paginated results using `->paginate(10)->withQueryString()`. The `withQueryString()` call preserves any query parameters (filters, ordering) in the pagination links so that the next/previous page links remain functional when filters are applied.

The page size of 10 is uniform across all resources and is consistent with the WordPress frontend's display requirements.

# 3. Individual Controller Logic

## 3.1. BookingController

Booking creation performs four sequential domain validations before creating the record, each with its own early-return response:

| Validation | HTTP Status on Failure | Reason |
| --- | --- | --- |
| Class is cancelled | `422` | No bookings should be created on cancelled sessions |
| Class is full | `422` | Capacity check using `GymClass::isFull()` |
| User is blocked from booking | `422` | Respects the strike-based blocking system |
| User already booked this class | `422` | Prevents duplicate booking of the same session |

Cancellation enforces a 2-hour window using `Booking::isCancellable()`. A booking cancelled within 2 hours of the class start time is rejected with `422`. This rule belongs in the model so it applies regardless of which code path triggers the cancellation.

Every booking creation and cancellation writes a row to `booking_history` to maintain a full state transition audit trail.

## 3.2. GymClassController

Class creation includes two cross-record conflict validations beyond basic field validation:

| Validation | What It Checks |
| --- | --- |
| Room conflict | Whether the room is already occupied in the requested time slot |
| Instructor conflict | Whether the instructor is already assigned to another class in the same slot |

Both checks are performed inside the controller using model scope queries before the class record is created. If either conflict exists, the creation is rejected with a descriptive `422` response. This logic is intentionally in the controller (not the Form Request) because it requires querying related records, not just validating field formats.

## 3.3. StaffAttendanceController

Clock-in creates a `StaffAttendance` record automatically using the authenticated user's `id` and `current_gym_id`. The user does not submit these fields — they are injected server-side. This prevents a staff member from clocking in on behalf of a different gym or a different user.

Clock-out finds the most recent open attendance record for the current user and sets `clock_out` to `Carbon::now()`. If no open record exists, the operation returns an error.

## 3.4. UserController

Provides administrative endpoints for:

| Action | Who Can Use It | What It Does |
| --- | --- | --- |
| `block` | Admin or manager | Sets `is_blocked_from_booking = true` |
| `unblock` | Admin or manager | Resets `is_blocked_from_booking = false` and clears strikes |
| `resetStrikes` | Admin | Sets `cancellation_strikes = 0` without unblocking |

These actions are separate endpoints rather than generic `update` calls to make authorization more explicit and to avoid exposing mass-assignment of sensitive fields through the general update flow.

## 3.5. NotificationController

Notification creation resolves its recipient list based on `target_audience`:

| Audience Value | Recipient Resolution |
| --- | --- |
| `global` | All active users |
| `staff_only` | All users with `advanced` role |
| `specific_gym` | All users whose `current_gym_id` matches `related_gym_id` |
| `specific_user` | Single user identified by ID in the request |

Each resolved recipient gets a corresponding row in `notification_delivery_logs` at creation time, allowing delivery and read status to be tracked independently per user.

# 4. Infrastructure and Fixes

## 4.1. Base Controller Fix (v0.9.0)

The base `Controller.php` was updated to extend `Illuminate\Routing\Controller` and apply the `AuthorizesRequests` trait. Without this, the `$this->authorize()` helper is unavailable on all subclasses, causing a fatal runtime error on the first policy-protected endpoint.

This is documented in detail in `laravel_security_and_resources.md`, section 4.1.

## 4.2. Response Consistency

Before the v0.8.0 standardization, some controller methods returned the model directly on success and a different structure on failure. This meant the frontend would need to handle two different response shapes for the same endpoint. All 18 controllers were normalized to always return `{ result: ..., message: { general: ... } }` regardless of outcome.

## 4.3. Route Verification

Route integrity was verified via `php artisan route:list`. Total active routes: **105**, distributed across all 18 resource controllers and the auth controller.

# 5. Sanitization Helpers

## 5.1. Why Helper Functions

Before any user-submitted string is used in a database scope or filter query, it passes through a sanitization helper. These are plain PHP functions defined globally and used selectively in controllers that accept filter parameters.

| Helper | Purpose |
| --- | --- |
| `limpiarCampo($value)` | Strips potentially dangerous characters from free-text filter inputs |
| `limpiarOrden($value)` | Restricts sort direction values to `asc` or `desc` — prevents SQL injection via ORDER BY |
| `limpiarNumeros($value)` | Casts input to numeric type before use in range queries |

> As of v0.10.0, these helpers were removed from most controllers in favor of passing raw validated input from `StoreXxxRequest` directly to Eloquent scopes, which use parameter binding internally. They remain in controllers that accept arbitrary filter strings not covered by a Form Request.

# 6. Code Comments

* No inline comments (`//`) in controller logic.
* JavaDoc on every public controller method documenting the access level, parameters, and return type.
