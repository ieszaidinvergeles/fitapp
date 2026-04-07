# Security Layer and API Resources — Laravel 11 FitApp Backend

**Date:** 2026-04-07 (Release: `v0.9.0-policies-security`) &nbsp;|&nbsp; **Updated:** 2026-04-07 (Release: `v0.10.0-resources`)

# 1. Terminology Reference


## 1.1. Authentication vs Authorization

| Concept | Definition | Where It Lives in FitApp |
|---|---|---|
| **Authentication** | Verifying *who* the user is | `auth:sanctum` middleware in `routes/api.php` |
| **Authorization** | Verifying *what* the user is allowed to do | Laravel Policies evaluated per controller action |

These are always separate concerns. Authentication runs first (via middleware). Authorization runs inside the controller method (via `$this->authorize()`).

## 1.2. Laravel Gate

The **Gate** is the central authorization broker in Laravel. Every `$this->authorize()` call passes through the Gate. The Gate:

1. Checks `Gate::before()` hooks first.
2. Resolves the matching Policy class for the model being checked.
3. Calls the corresponding Policy method.
4. Returns `true` (allow) or `false` (deny).

> `Gate::before()` runs *before* any Policy method is evaluated. Returning `true` from it short-circuits all further evaluation. Returning `null` tells the Gate to continue. Returning `false` would deny unconditionally, regardless of the policy.

## 1.3. Laravel Policy

A **Policy** is a PHP class that groups all authorization logic for a single Eloquent model. Instead of checking permissions inside controllers, the controller delegates to the policy via `$this->authorize('action', $model)`.

Laravel resolves policies automatically by naming convention: `UserPolicy` handles the `User` model, `BookingPolicy` handles `Booking`, and so on. All 18 policies are registered automatically via the `#[Policy]` convention in Laravel 11 (no manual registration needed).

## 1.4. `$this->authorize()`

This is a controller helper method that:

1. Takes an action string (`'view'`, `'create'`, `'update'`, `'delete'`, `'viewAny'`) and a model instance or class.
2. Runs the corresponding method in the resolved Policy.
3. If the policy returns `false`, it throws `Illuminate\Auth\Access\AuthorizationException`, which Laravel converts to an HTTP 403 Forbidden response.
4. If the policy returns `true`, execution continues normally.

> `$this->authorize()` is provided by the `AuthorizesRequests` trait from `Illuminate\Foundation\Auth\Access`. It is **not** available on a plain PHP class — the controller must extend `Illuminate\Routing\Controller` and use this trait.

## 1.5. API Resource

An **API Resource** (class extending `Illuminate\Http\Resources\Json\JsonResource`) acts as a transformation layer between an Eloquent model and the JSON response sent to the client.

Without a Resource, returning a model directly serializes every database column, including sensitive fields like `password_hash` or `remember_token`. A Resource defines an explicit whitelist of fields that are safe to expose.

## 1.6. `whenLoaded()`

`whenLoaded('relationName')` is a guard method used inside `toArray()` in a Resource. It serializes a related model only if it was eagerly loaded via `->with('relation')` in the query. If the relation was not loaded, the field is simply omitted from the JSON output.

**Why this matters:** Without this guard, accessing `$this->gymClass` inside a Resource would trigger an additional database query for every item in a paginated list. This is the **N+1 query problem**. With `whenLoaded()`, the caller decides whether to pay the cost of loading relations, making it opt-in.

## 1.7. Multi-Tenancy

FitApp hosts multiple gyms (tenants) in a single shared database. Each `User` with role `manager` or `staff` has a `current_gym_id` column indicating which gym they belong to. Multi-tenancy enforcement means that these users must only be able to create, read, update, or delete records that belong to their own gym.

This is enforced throughout the Policies using the pattern:

```php
$user->isAdvanced() && $user->current_gym_id === $resource->gym_id
```

## 1.8. User Roles

| Role | `isAdmin()` | `isAdvanced()` | Description |
|---|---|---|---|
| `admin` | `true` | `true` | Global administrator. Access to everything. |
| `manager` | `false` | `true` | Manages a single assigned gym. |
| `staff` | `false` | `true` | Operates within a single assigned gym. |
| `client` | `false` | `false` | Standard gym member. Self-service only. |
| `user_online` | `false` | `false` | Online membership. No physical gym access. |

> `isAdmin()` checks `role === 'admin'`. `isAdvanced()` checks `role === 'manager'` OR `role === 'staff'`. These are helper methods defined on the `User` model.

---

# 2. Architecture and Request Lifecycle

Every authorized request follows this path:

```
HTTP Request
  → Middleware: auth:sanctum (Authentication)
    → Controller method
      → $this->authorize('action', $model)
        → Gate::before() — if admin: grant immediately
          → Policy::action($user, $model) — role/ownership check
            → AuthorizationException (403) OR continue
              → Business logic
                → Return Resource (sanitized JSON)
```

---

# 3. Phase v0.9.0 — Security Layer

## 3.1. Base `Controller.php` Fix

**Location:** `app/Http/Controllers/Controller.php`

**Problem before this fix:** The `Controller.php` stub generated by Laravel 11's Artisan does not extend `Illuminate\Routing\Controller` by default. This means `$this->authorize()` does not exist on any controller, causing a fatal runtime error.

**Solution:**

```php
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controller as BaseController;

abstract class Controller extends BaseController
{
    use AuthorizesRequests;
}
```

**Why this approach:** Extending the base Routing Controller and applying the trait once in the abstract base class applies the fix uniformly to all 18 feature controllers without modifying any individual controller. This follows OCP (Open/Closed Principle): the abstract class is extended but not modified by feature controllers.

> Without this fix, all `$this->authorize()` calls throw `BadMethodCallException: Call to undefined method` at runtime on the first request.

## 3.2. Global Admin Bypass — `AppServiceProvider`

**Location:** `app/Providers/AppServiceProvider.php`

```php
Gate::before(function (User $user, string $ability) {
    if ($user->isAdmin()) {
        return true;
    }
    return null;
});
```

**Why `Gate::before()`:** Without a centralized bypass, every single Policy method would require an explicit `if ($user->isAdmin()) return true;` check. With 18 models and an average of 5 Policy methods each, that is 90 duplicate lines of logic. The `Gate::before` hook centralizes this in one place. Adding or removing admin privileges requires changing only one line.

**Why return `null` instead of `false`:** Returning `null` tells the gate to proceed to the Policy. Returning `false` would deny access completely, including for admins. Only `true` (grant) and `null` (continue) are valid returns from `Gate::before` in this context.

## 3.3. Policy Design Patterns

All 18 Policies follow one of four patterns based on the resource type:

### Pattern A — Ownership (user can only access their own records)

**Used by:** `BodyMetricPolicy`, `SettingPolicy`, `UserMealSchedulePolicy`, `UserFavoritePolicy`

```php
public function view(User $user, BodyMetric $metric): bool
{
    return $user->id === $metric->user_id;
}
```

**Why:** These resources contain personal health or preference data. No user should be able to read or modify another user's body metrics or settings. The check is purely on record ownership.

### Pattern B — Multi-Tenant (advanced staff restricted to own gym)

**Used by:** `GymClassPolicy`, `RoomPolicy`, `GymPolicy`, `StaffAttendancePolicy`

```php
public function update(User $user, GymClass $gymClass): bool
{
    return $user->isAdvanced() && $user->current_gym_id === $gymClass->gym_id;
}
```

**Why:** A manager from gym A must not be able to modify classes, rooms, or attendance records from gym B. The `current_gym_id` column is the tenant boundary. Both conditions must be satisfied: the user is staff-level, AND the resource belongs to the same gym.

### Pattern C — Role-Based with Public Read

**Used by:** `ExercisePolicy`, `RecipePolicy`, `RoutinePolicy`, `NotificationPolicy`, `BookingPolicy`

```php
public function view(User $user, Exercise $exercise): bool
{
    return true; // public read
}

public function create(User $user): bool
{
    return $user->isAdvanced();
}

public function delete(User $user, Exercise $exercise): bool
{
    return false; // admin only (handled by Gate::before)
}
```

**Why:** Exercises and recipes are catalogue data — readable by all authenticated users, writable only by staff, deletable only by admins. The `delete()` returning `false` is intentional: it will only return `true` for admins because `Gate::before()` intercepts the call before the policy runs.

### Pattern D — Admin-Exclusive Mutations

**Used by:** `ActivityPolicy`, `EquipmentPolicy`, `MembershipPlanPolicy`, `DietPlanPolicy`

```php
public function create(User $user): bool
{
    return false; // only admin (Gate::before grants it)
}
```

**Why:** These are global catalogue entities that define what FitApp offers. Only the global admin should be able to create, update, or delete them. Because `Gate::before` grants admins access before any policy runs, it is correct to return `false` here — non-admins are denied, admins bypass the policy entirely.

## 3.4. Manual Checks Eliminated from Controllers

Before v0.9.0, authorization was done inline inside controller methods:

```php
// Before: manual and not centralized
if (!$request->user()->isAdmin() && $request->user()->id !== $booking->user_id) {
    return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
}
```

After v0.9.0, this is replaced everywhere with:

```php
// After: delegated to BookingPolicy::view()
$this->authorize('view', $booking);
```

The grep search `if.*isAdmin()` across all controllers returns zero results after this refactoring.

---

# 4. Phase v0.10.0 — API Resources

## 4.1. Why Resources Instead of Raw Models

| Problem with raw models | Solution with Resources |
|---|---|
| All 60+ columns exposed | Only whitelisted fields included |
| `password_hash` leaks in User responses | Excluded from `UserResource::toArray()` |
| Dates returned as raw strings | Formatted as ISO 8601 via `->toIso8601String()` |
| Relations trigger N+1 queries if auto-serialized | `whenLoaded()` makes relations opt-in |
| Internal column names exposed (e.g. `macros_json`) | Renamed to `macros` in the API contract |

## 4.2. Date Formatting Convention

All date and datetime fields in Resources follow this rule:

| Field Type | Output Format | Method Used |
|---|---|---|
| Date-only (birthdate, metric date) | `"2025-04-07"` | `->toDateString()` |
| Date + time (bookings, clock-in, notifications) | `"2025-04-07T09:00:00+00:00"` | `->toIso8601String()` |

> This prevents inconsistent timestamp formats from reaching the WordPress frontend, where date parsing can fail silently.

## 4.3. Sensitive Field Exclusion

The following fields are **never** included in any Resource response:

| Model | Excluded Fields |
|---|---|
| `User` | `password_hash`, `remember_token` |

No other model stores credentials or tokens. All other excluded fields are simply omitted by not listing them in `toArray()` (e.g., internal pivot join columns, `created_at`/`updated_at` where not relevant).

## 4.4. `whenLoaded()` Usage Map

Relations are conditionally loaded. The controller must call `->with('relation')` explicitly to include a nested Resource in the response.

| Resource | Conditional Relations |
|---|---|
| `UserResource` | `currentGym` (GymResource), `membershipPlan` (MembershipPlanResource) |
| `BookingResource` | `gymClass` (GymClassResource), `user` (UserResource) |
| `GymClassResource` | `gym`, `activity`, `instructor` (UserResource), `room` |
| `GymResource` | `manager` (UserResource) |
| `RoomResource` | `gym` (GymResource) |
| `RoutineResource` | `creator` (UserResource), `exercises` (ExerciseResource collection) |
| `StaffAttendanceResource` | `staff` (UserResource), `gym` (GymResource) |
| `NotificationResource` | `sender` (UserResource) |
| `UserMealScheduleResource` | `recipe` (RecipeResource) |

## 4.5. Resource–Controller Pairing

All 18 controllers were updated to return Resource instances on reads and Resource collections on paginated lists.

| Controller | Resource | Collection Method |
|---|---|---|
| `UserController` | `UserResource` | `UserResource::collection(...)` |
| `BookingController` | `BookingResource` | `BookingResource::collection(...)` |
| `GymClassController` | `GymClassResource` | `GymClassResource::collection(...)` |
| `GymController` | `GymResource` | `GymResource::collection(...)` |
| `RoomController` | `RoomResource` | `RoomResource::collection(...)` |
| `ActivityController` | `ActivityResource` | `ActivityResource::collection(...)` |
| `EquipmentController` | `EquipmentResource` | `EquipmentResource::collection(...)` |
| `ExerciseController` | `ExerciseResource` | `ExerciseResource::collection(...)` |
| `MembershipPlanController` | `MembershipPlanResource` | `MembershipPlanResource::collection(...)` |
| `RoutineController` | `RoutineResource` | `RoutineResource::collection(...)` |
| `RecipeController` | `RecipeResource` | `RecipeResource::collection(...)` |
| `DietPlanController` | `DietPlanResource` | `DietPlanResource::collection(...)` |
| `BodyMetricController` | `BodyMetricResource` | `BodyMetricResource::collection(...)` |
| `NotificationController` | `NotificationResource` | `NotificationResource::collection(...)` |
| `StaffAttendanceController` | `StaffAttendanceResource` | `StaffAttendanceResource::collection(...)` |
| `SettingController` | `SettingResource` | — (single record only) |
| `UserFavoriteController` | `UserFavoriteResource` | `UserFavoriteResource::collection(...)` |
| `UserMealScheduleController` | `UserMealScheduleResource` | `UserMealScheduleResource::collection(...)` |

---

# 5. Fix Report

## 5.1. Base Controller Missing `AuthorizesRequests` Trait

- **Error:** `BadMethodCallException: Call to undefined method App\Http\Controllers\UserController::authorize()` on every policy-protected endpoint.
- **Root cause:** The default `Controller.php` in Laravel 11 does not extend `Illuminate\Routing\Controller`, which is required to access the `AuthorizesRequests` trait.
- **Fix:** Extended `Controller.php` from `Illuminate\Routing\Controller` and applied `use AuthorizesRequests;`.
- **Scope:** This single fix resolves authorization for all 18 controllers simultaneously.

## 5.2. Hardcoded Role Checks in Controllers

- **Error:** Authorization logic duplicated inside multiple controller methods (`UserController`, `BookingController`, `StaffAttendanceController`, `UserMealScheduleController`, `UserFavoriteController`).
- **Root cause:** Policies were not yet in place during initial scaffolding.
- **Fix:** All `if (!$user->isAdmin())` and `if ($user->id !== $record->user_id)` guards replaced with `$this->authorize()` delegates.
- **Verification:** `grep -r "if.*isAdmin()" app/Http/Controllers/` returns zero results.

## 5.3. Raw Model Returns in Controllers

- **Error:** All 18 controllers returned raw Eloquent models, exposing all database columns including `password_hash`.
- **Fix:** Wrapping all single-record returns in `new XxxResource($model)` and all paginated returns in `XxxResource::collection($paginator)`.

---

# 6. What Was Intentionally Deferred

The following improvements are technically correct but do not block frontend integration. They are deferred to avoid scope creep:

| Feature | Reason Deferred |
|---|---|
| **Observers** for audit/log automation | Does not affect returned data or security |
| **Soft deletes** on User and Booking | Requires schema migration; not blocking |
| **Rate limiting** middleware | Not critical during initial WordPress integration |
| **Eager loading in controllers** | Will be added per endpoint as frontend needs become clear |
| **API versioning** (`/api/v1/`) | Only needed when breaking changes are introduced |
