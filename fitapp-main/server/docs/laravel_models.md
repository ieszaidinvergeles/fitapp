# Gym — Models Implementation and Domain Logic

**Date:** 2026-04-06 (Release: `v0.7.0-models`)

# 1. Domain Model Philosophy

## 1.1. Fat Models, Thin Controllers

The Voltgym backend follows the **Fat Model** pattern. Business rules, validation logic, and state management are encapsulated inside the Eloquent model that owns them. Controllers are responsible only for receiving requests and returning responses — they do not make business decisions.

The practical consequence is that any code that changes, blocks, or derives a value from a model record must go through a model method, not be duplicated across controllers. If the rule changes, it changes in one place.

## 1.2. Domain Encapsulation

Sensitive fields — strikes, blocked status, roles, password hashes — are never updated by assigning them directly from controller request data. All mutations to these fields happen through dedicated model methods (`incrementStrike()`, `blockIfNeeded()`, `cancel()`) that enforce business rules as part of the mutation. This prevents accidental or malicious mass-assignment of protected fields.

# 2. Core Identity and Operations

## 2.1. User

The central entity of the system. Every other domain object is either owned by, assigned to, or interacts with a User.

| Responsibility | Details |
| --- | --- |
| Identity | `email`, `dni`, `full_name`, `birth_date` |
| Authentication | `password_hash`, `remember_token`, `email_verified_at` |
| Role and access | `role` (enum), helper methods `isAdmin()`, `isAdvanced()`, `isUser()` |
| Membership | `membership_plan_id`, `membership_status`, `current_gym_id` |
| Booking discipline | `cancellation_strikes`, `is_blocked_from_booking` |

### Business Methods

| Method | Trigger | Effect |
| --- | --- | --- |
| `incrementStrike()` | Booking cancellation within 2 hours | Increments `cancellation_strikes` by 1 |
| `blockIfNeeded()` | Called after `incrementStrike()` | Sets `is_blocked_from_booking = true` if strikes reach the threshold |
| `isBlockedFromBooking()` | Booking creation | Returns the current blocked status |

## 2.2. Gym

Represents a physical gym facility. It is the tenancy boundary — all staff, classes, rooms, and equipment belong to a specific gym.

| Responsibility | Details |
| --- | --- |
| Identity | `name`, `address`, `city`, `location_coords`, `phone` |
| Management | `manager_id` — the User assigned as manager |
| Relationships | Has many `rooms`, `classes`, `staff_attendance`, notifications via `related_gym_id` |

> The `manager_id` relationship is circular with `users.current_gym_id`. This is resolved through a three-migration strategy. See `laravel_database_review.md` section 1.5 for the full explanation.

## 2.3. GymClass

Represents a scheduled instance of an Activity, held in a Room, led by an instructor (User).

| Responsibility | Details |
| --- | --- |
| Scheduling | `start_time`, `end_time` (Carbon datetime) |
| Capacity | `capacity_limit` |
| State | `is_cancelled` (boolean) |

### Business Methods

| Method | Returns | Logic |
| --- | --- | --- |
| `isFull()` | `bool` | Compares count of active bookings against `capacity_limit` |
| `isUserBooked(int $userId)` | `bool` | Checks whether the user already has an active booking for this class |
| `markAttended(int $userId)` | `void` | Updates the booking status to `attended` for the given user |
| `cancel()` | `void` | Sets `is_cancelled = true` and cascades cancellation to active bookings |

## 2.4. Room

Represents a physical space inside a gym used for classes.

| Responsibility | Details |
| --- | --- |
| Identity | `name`, `capacity` |
| Tenancy | `gym_id` — always scoped to a specific gym |
| Conflict detection | `hasConflict(Carbon $start, Carbon $end)` — checks for overlapping class schedules |

### Why `hasConflict()` Lives in the Model

Room scheduling conflicts are a domain rule: two classes cannot occupy the same room at the same time. By placing this check in the `Room` model, any code path that creates a class (controller, seeder, command) can call the same validation. Duplicating this logic in the controller would mean it is not enforced in other contexts.

# 3. Training and Personalization

## 3.1. Routine

A multi-exercise training plan created by advanced staff or the admin, assignable to clients.

| Responsibility | Details |
| --- | --- |
| Metadata | `name`, `difficulty_level` (enum), `estimated_duration_min` |
| Authorship | `creator_id` — the User who created it |
| Nutrition link | `associated_diet_plan_id` — optional link to a DietPlan |

### Business Methods

| Method | Purpose |
| --- | --- |
| `orderedExercises()` | Returns the exercises sorted by their `order` pivot column |
| `duplicate()` | Creates a deep copy of the routine and its exercise assignments for a given user |

### Exercise Association via Pivot

Routines use a `belongsToMany` relationship with `Exercise` through the `routine_exercises` pivot table. The pivot stores an `order` column and a `notes` column. The `orderedExercises()` relationship applies `->withPivot(['order', 'notes'])->orderBy('routine_exercises.order')` to always return exercises in the intended workout sequence.

## 3.2. BodyMetric

A point-in-time snapshot of a user's physical measurements.

| Field | Cast | Purpose |
| --- | --- | --- |
| `date` | `date` | Carbon date-only object |
| `weight_kg` | `decimal:1` | One decimal precision for body weight |
| `height_cm` | `decimal:1` | One decimal precision for height |
| `body_fat_pct` | `decimal:2` | Two decimal precision for percentage |
| `muscle_mass_pct` | `decimal:2` | Two decimal precision for percentage |

### Business Methods

| Method | Returns | Logic |
| --- | --- | --- |
| `bmi()` | `float` | Computes `weight_kg / (height_m)²` from stored values |
| `deltaFrom(BodyMetric $previous)` | `array` | Returns signed differences for each measurement between two snapshots |

> `deltaFrom()` is used by the frontend to display progress indicators (e.g., "+1.5 kg since last measurement"). It takes the previous metric as a parameter rather than querying internally to avoid hidden N+1 queries.

## 3.3. UserMealSchedule

A calendar entry linking a User to a Recipe on a specific date and meal slot.

| Field | Cast | Purpose |
| --- | --- | --- |
| `date` | `date` | Carbon date-only |
| `meal_type` | `string` (enum) | `breakfast`, `lunch`, `dinner`, `snack`, `pre_workout`, `post_workout` |
| `is_consumed` | `boolean` | Whether the user marked the meal as eaten |

### Business Methods

| Method | Returns | Logic |
| --- | --- | --- |
| `totalCaloriesForDate(int $userId, Carbon $date)` | `int` | Sums calories from all consumed recipes for a given user-date pair |

## 3.4. Notification

Audience-targeted broadcast from staff to users, with delivery tracked per recipient.

| Audience Value | Who Receives It |
| --- | --- |
| `global` | All active users |
| `staff_only` | All users with `advanced` role |
| `specific_gym` | All users whose `current_gym_id` matches `related_gym_id` |
| `specific_user` | Single user identified at creation |

### Business Methods

| Method | Returns | Logic |
| --- | --- | --- |
| `resolveRecipients()` | `Collection<User>` | Returns the set of User records that should receive the notification, based on `target_audience` |

# 4. Nutrition and Health

## 4.1. MembershipPlan

Defines pricing tiers and feature entitlements available for user subscription.

| Field | Cast | Note |
| --- | --- | --- |
| `price` | `decimal:2` | Financial precision — always two decimal places |
| `allow_partner_link` | `boolean` | Whether this plan allows a secondary linked account (duo plan) |
| `type` | string (enum) | `physical`, `online`, or `duo` |

## 4.2. Recipe

A nutritional content item (meal, snack) with macro breakdown and calorie count.

| Field | Cast | Note |
| --- | --- | --- |
| `calories` | `integer` | Total calorie count |
| `macros_json` | `array` | JSON cast — stored as string in DB, accessed as PHP array in code |

The `macros_json` field is exposed in `RecipeResource` under the alias `macros` to present a cleaner API surface. The internal column name `macros_json` communicates to developers that the column contains raw JSON.

## 4.3. DietPlan

A high-level nutritional objective (bulk, cut, maintenance) that can be associated with a Routine. It does not define specific meals — those are managed by `UserMealSchedule` and `Recipe`. The plan serves as a label and goal anchor.

# 5. Infrastructure and Support

## 5.1. Activity

Defines the type of exercise a GymClass offers (e.g., Yoga, HIIT, Pilates). It is a catalogue entity — read by all, mutated only by admin.

| Field | Note |
| --- | --- |
| `intensity_level` | Enum: `low`, `medium`, `high`, `extreme` |
| `image_url` | URL to a representative image, 500 char max |

## 5.2. Equipment

Tracks individual pieces of gym equipment and their availability status.

| Field | Cast | Note |
| --- | --- | --- |
| `is_home_accessible` | `boolean` | Whether the equipment can be replicated at home for remote training |

Equipment is linked to gyms via the `gym_inventory` pivot table. The pivot stores `quantity` and `status` (operational, maintenance, retired) per gym. No dedicated `GymInventory` model exists — the pivot is accessed entirely through the `Gym` and `Equipment` `belongsToMany` relationships using `->withPivot(['quantity', 'status'])`.

## 5.3. Exercise

An atomic, documented movement with instructions and multimedia.

| Field | Note |
| --- | --- |
| `target_muscle_group` | Enum with 20 possible values — see `laravel_database_review.md` section 1.2 |
| `image_url` | 500 char max |
| `video_url` | 500 char max |

Exercises have no direct ownership — they are global catalogue entries usable in any Routine.

## 5.4. Setting

A strict one-to-one personal preferences record for each user. The `user_id` column is simultaneously the Primary Key and the Foreign Key, making it structurally impossible to have more than one settings record per user.

| Field | Cast | Purpose |
| --- | --- | --- |
| `language_preference` | `string` | `es` or `en` |
| `theme_preference` | `boolean` | Light/dark mode |
| `share_workout_stats` | `boolean` | Privacy control for workout history |
| `share_body_metrics` | `boolean` | Privacy control for body metrics |
| `share_attendance` | `boolean` | Privacy control for gym attendance |

A `Setting` row is created automatically during user registration inside a database transaction. If the settings row fails to insert, the user record is also rolled back.

## 5.5. UserFavorite

A polymorphic bookmark system allowing users to mark gyms, activities, or routines as favourites.

| Field | Note |
| --- | --- |
| `entity_type` | Enum: `gym`, `activity`, `routine` |
| `entity_id` | ID of the favourited record in the corresponding table |

The composite Primary Key `(user_id, entity_type, entity_id)` ensures a user cannot bookmark the same record twice. The `entity_id` column uses `unsignedBigInteger` without a foreign key because a relational database engine cannot enforce a FK that points to different tables depending on a sibling column's value.

# 6. Relationship Architecture Summary

| Source Model | Relationship | Target Model | Pivot Table / Key |
| --- | --- | --- | --- |
| `User` | `hasOne` | `Setting` | `user_id` (Shared PK/FK) |
| `User` | `belongsTo` | `Gym` | `current_gym_id` (semantic name) |
| `User` | `belongsTo` | `MembershipPlan` | `membership_plan_id` |
| `Gym` | `belongsTo` | `User` | `manager_id` (semantic name) |
| `GymClass` | `belongsTo` | `Gym`, `Activity`, `Room`, `User` | `gym_id`, `activity_id`, `room_id`, `instructor_id` |
| `Booking` | `belongsTo` | `GymClass`, `User` | `class_id`, `user_id` |
| `Routine` | `belongsToMany` | `Exercise` | `routine_exercises` (with `order`, `notes` pivot cols) |
| `Routine` | `belongsToMany` | `User` | `user_active_routines` |
| `User` | `belongsToMany` | `User` | `user_partners` |
| `Gym` | `belongsToMany` | `Equipment` | `gym_inventory` (with `quantity`, `status` pivot cols) |

# 7. Log Models

## 7.1. Namespace and Separation

All immutable log models live under `App\Models\logs\`. This separates them from the live domain models both conceptually and in IDE navigation. See `laravel_database_logs_review.md` for the complete rationale.

| Model | Table | Purpose |
| --- | --- | --- |
| `AuditLog` | `audit_logs` | Generic data change history |
| `AuthLog` | `auth_logs` | Authentication events |
| `ConsentLog` | `consent_logs` | GDPR consent records |
| `BookingHistory` | `booking_history` | Booking state transitions |
| `AdminActionLog` | `admin_action_logs` | Sensitive admin actions |
| `NotificationDeliveryLog` | `notification_delivery_logs` | Per-recipient delivery tracking |

## 7.2. Shared Configuration

All log models share this configuration:

| Property | Value |
| --- | --- |
| `$timestamps` | `false` — only `created_at`, managed via DB default |
| `$fillable` | Explicit array — no `$guarded = []` shortcut |
| No relationships | No `belongsTo` or `hasMany` — see `laravel_database_logs_review.md` section 3 |

# 8. Fix Report

## 8.1. Immutable Logs Namespace Consolidation

* **Problem:** Log model files were initially generated in `app/Models/` alongside domain models, making it difficult to distinguish mutable from immutable entities.
* **Fix:** Moved all six log models to `app/Models/logs/` with the namespace `App\Models\logs\`.
* **Impact:** All controller references, factory definitions, and migration seeders updated to use the new namespace.

## 8.2. Data Casting Standardization

* **Problem:** Several models were returning booleans as `"0"` and `"1"` strings, dates as raw MySQL datetime strings, and JSON fields as plain text.
* **Fix:** Added explicit `$casts` entries for all boolean, datetime, date, and JSON fields across all 18 models.
* **Impact:** Frontend consumers receive correctly typed data without needing client-side type coercion.

## 8.3. `$guarded` vs `$fillable`

* **Problem:** Some models used `protected $guarded = []` which disables all mass-assignment protection.
* **Fix:** Replaced with explicit `$fillable` arrays on all models. Only fields that should be mass-assignable are listed.
* **Impact:** Sensitive fields (`role`, `password_hash`, `cancellation_strikes`, `is_blocked_from_booking`) are protected from mass-assignment through controller requests.

# 9. Code Comments

* No inline comments (`//`) in model property declarations.
* JavaDoc on every public business method explaining its input, output, and the business rule it enforces.
