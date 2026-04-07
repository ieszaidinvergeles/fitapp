# Gym — API Request Validators

**Date:** 2026-03-23 (Release: `v0.5.0-validators`)

# 1. Validation Architecture

## 1.1. Why Dedicated Form Request Classes

Laravel allows validating input directly inside controller methods using `$request->validate([...])`. This approach was deliberately avoided in FitApp. A dedicated `FormRequest` class for each operation provides:

* **SRP compliance:** The controller is not responsible for knowing which fields are required or what format they should be in.
* **Reusability:** Multiple controller methods or command-line tools can use the same request class.
* **Testability:** Validation rules can be unit tested independently of HTTP infrastructure.
* **Clarity:** Reading a controller method tells you what it does, not how it validates input.

> `$request->validated()` returns only the fields that passed validation — any extra fields submitted by the client are silently discarded. This means there is no need to manually whitelist fields before passing them to `Model::create()`.

## 1.2. General Conventions

* All request classes extend `Illuminate\Foundation\Http\FormRequest`.
* The `authorize()` method returns `true` in all Form Request classes — authorization is handled separately by the Policy layer.
* Each class handles one and only one user-facing operation (store, update, login, etc.).
* Controllers always receive data via `$request->validated()`, never `$request->all()` or `$request->input()`.

# 2. Auth Validators

## 2.1. RegisterRequest

| Field | Rules | Explanation |
| --- | --- | --- |
| `full_name` | `required`, `string`, `max:160` | Matches the `full_name` column length |
| `email` | `required`, `email`, `unique:users`, `max:160` | Must not already exist in the system |
| `password` | `required`, `string`, `min:8`, `max:255`, regex | Must contain at least one uppercase letter, one digit, and one special character |
| `password_confirmation` | `required`, `same:password` | Client-side double-entry confirmation |
| `dni` | `required`, `string`, `size:9`, `unique:users`, regex | Spanish DNI format: 8 digits followed by one letter |
| `birth_date` | `required`, `date`, `before:2010-01-01` | Minimum age of 16 years at time of registration |

> The DNI regex enforces the exact Spanish national identity document format. The `unique:users` rule on both `email` and `dni` is enforced at the database level as well (unique index), but the FormRequest catches duplicates before hitting the DB to return a user-friendly error instead of a database exception.

## 2.2. LoginRequest

| Field | Rules | Explanation |
| --- | --- | --- |
| `email` | `required`, `email`, `max:160` | Field identity — format only, not existence |
| `password` | `required`, `string` | No format restriction — bcrypt comparison handles it |

> Whether the email exists or not is deliberately not validated here. Returning a different error for "email not found" vs "wrong password" would confirm to an attacker which emails are registered (user enumeration). The controller returns a generic credentials error regardless of which field is wrong.

## 2.3. ForgotPasswordRequest

| Field | Rules | Explanation |
| --- | --- | --- |
| `email` | `required`, `email`, `exists:users`, `max:160` | Must correspond to a real account to initiate reset |

## 2.4. ResetPasswordRequest

| Field | Rules | Explanation |
| --- | --- | --- |
| `email` | `required`, `email`, `exists:users`, `max:160` | Identifies the account being reset |
| `token` | `required`, `string` | The signed token from the reset email |
| `password` | `required`, `string`, `min:8`, `max:255` | New password — same minimum security as registration |
| `password_confirmation` | `required`, `same:password` | Prevents typos on the new password |

# 3. Resource Validators

All 18 domain resources have two associated request classes: `StoreXxxRequest` (for creation) and `UpdateXxxRequest` (for modification). The split exists because create operations typically require all fields while update operations allow partial modification.

## 3.1. User

### StoreUserRequest

| Field | Rules |
| --- | --- |
| `full_name` | `required`, `string`, `max:160` |
| `email` | `required`, `email`, `unique:users`, `max:160` |
| `password` | `required`, `string`, `min:8`, `max:255` |
| `role` | `required`, `in:admin,manager,staff,client,user_online` |
| `dni` | `required`, `string`, `size:9`, `unique:users` |
| `birth_date` | `required`, `date`, `before:2010-01-01` |
| `membership_plan_id` | `nullable`, `exists:membership_plans,id` |
| `current_gym_id` | `nullable`, `exists:gyms,id` |

### UpdateUserRequest

| Field | Rules |
| --- | --- |
| `full_name` | `sometimes`, `string`, `max:160` |
| `phone` | `sometimes`, `nullable`, `string`, `max:20` |
| `profile_image_url` | `sometimes`, `nullable`, `string`, `max:500` |
| `membership_plan_id` | `sometimes`, `nullable`, `exists:membership_plans,id` |
| `current_gym_id` | `sometimes`, `nullable`, `exists:gyms,id` |

> `sometimes` means the rule only applies if the field is present in the request. This allows partial updates — the client sends only the fields it wants to change.

## 3.2. Gym

### StoreGymRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `address` | `required`, `string`, `max:160` |
| `city` | `required`, `string`, `max:80` |
| `phone` | `nullable`, `string`, `max:20` |
| `location_coords` | `nullable`, `string`, `max:100` |

### UpdateGymRequest

Same fields as Store, all with `sometimes` instead of `required`.

## 3.3. Room

### StoreRoomRequest

| Field | Rules |
| --- | --- |
| `gym_id` | `required`, `exists:gyms,id` |
| `name` | `required`, `string`, `max:80` |
| `capacity` | `required`, `integer`, `min:1` |

## 3.4. GymClass

### StoreGymClassRequest

| Field | Rules |
| --- | --- |
| `gym_id` | `required`, `exists:gyms,id` |
| `activity_id` | `required`, `exists:activities,id` |
| `instructor_id` | `required`, `exists:users,id` |
| `room_id` | `required`, `exists:rooms,id` |
| `start_time` | `required`, `date_format:Y-m-d H:i:s` |
| `end_time` | `required`, `date_format:Y-m-d H:i:s`, `after:start_time` |
| `capacity_limit` | `required`, `integer`, `min:1` |

> The `after:start_time` rule enforces that `end_time` is always chronologically after `start_time`. This is a temporal integrity constraint that does not exist at the database level, so it must be enforced here.

## 3.5. Activity

### StoreActivityRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `description` | `nullable`, `string`, `max:2000` |
| `intensity_level` | `required`, `in:low,medium,high,extreme` |
| `image_url` | `nullable`, `string`, `max:500` |

## 3.6. Equipment

### StoreEquipmentRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `description` | `nullable`, `string`, `max:2000` |
| `is_home_accessible` | `required`, `boolean` |
| `image_url` | `nullable`, `string`, `max:500` |

## 3.7. Exercise

### StoreExerciseRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `description` | `nullable`, `string`, `max:2000` |
| `target_muscle_group` | `required`, enum value from the 20 allowed muscle groups |
| `image_url` | `nullable`, `string`, `max:500` |
| `video_url` | `nullable`, `string`, `max:500` |

## 3.8. Routine

### StoreRoutineRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `description` | `nullable`, `string`, `max:2000` |
| `difficulty_level` | `required`, `in:beginner,intermediate,advanced,expert` |
| `estimated_duration_min` | `required`, `integer`, `min:5`, `max:300` |
| `associated_diet_plan_id` | `nullable`, `exists:diet_plans,id` |

## 3.9. Recipe

### StoreRecipeRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `type` | `required`, `in:breakfast,lunch,dinner,snack,pre_workout,post_workout` |
| `calories` | `required`, `integer`, `min:0`, `max:9999` |
| `macros_json` | `nullable`, `json` |
| `image_url` | `nullable`, `string`, `max:500` |

## 3.10. DietPlan

### StoreDietPlanRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `description` | `nullable`, `string`, `max:2000` |
| `goal` | `required`, `string`, `max:80` |

## 3.11. MembershipPlan

### StoreMembershipPlanRequest

| Field | Rules |
| --- | --- |
| `name` | `required`, `string`, `max:80` |
| `type` | `required`, `in:physical,online,duo` |
| `price` | `required`, `numeric`, `min:0` |
| `duration_months` | `required`, `integer`, `min:1` |
| `allow_partner_link` | `required`, `boolean` |
| `description` | `nullable`, `string`, `max:2000` |

## 3.12. BodyMetric

### StoreBodyMetricRequest

| Field | Rules |
| --- | --- |
| `date` | `required`, `date`, `before_or_equal:today` |
| `weight_kg` | `required`, `numeric`, `min:1`, `max:999.9` |
| `height_cm` | `nullable`, `numeric`, `min:50`, `max:999.9` |
| `body_fat_pct` | `nullable`, `numeric`, `min:0`, `max:99.99` |
| `muscle_mass_pct` | `nullable`, `numeric`, `min:0`, `max:99.99` |

## 3.13. Notification

### StoreNotificationRequest

| Field | Rules |
| --- | --- |
| `title` | `required`, `string`, `max:150` |
| `body` | `required`, `string`, `max:2000` |
| `target_audience` | `required`, `in:global,staff_only,specific_gym,specific_user` |
| `related_gym_id` | `required_if:target_audience,specific_gym`, `exists:gyms,id` |
| `specific_user_id` | `required_if:target_audience,specific_user`, `exists:users,id` |

> `required_if` enforces conditional requirements: `related_gym_id` is only required when the audience is `specific_gym`, and `specific_user_id` only when the audience is `specific_user`. This avoids nullable fields that could be silently ignored.

## 3.14. UserFavorite

### StoreUserFavoriteRequest

| Field | Rules |
| --- | --- |
| `entity_type` | `required`, `in:gym,activity,routine` |
| `entity_id` | `required`, `integer`, `min:1` |

## 3.15. UserMealSchedule

### StoreUserMealScheduleRequest

| Field | Rules |
| --- | --- |
| `date` | `required`, `date` |
| `meal_type` | `required`, `in:breakfast,lunch,dinner,snack,pre_workout,post_workout` |
| `recipe_id` | `nullable`, `exists:recipes,id` |
| `is_consumed` | `sometimes`, `boolean` |

## 3.16. StaffAttendance

No StoreRequest is used — the `gym_id` and `staff_id` are injected server-side from the authenticated user. The only update field is `clock_out`.

### UpdateStaffAttendanceRequest

| Field | Rules |
| --- | --- |
| `clock_out` | `required`, `date_format:Y-m-d H:i:s` |

## 3.17. Setting

### UpdateSettingRequest

| Field | Rules |
| --- | --- |
| `language_preference` | `sometimes`, `in:es,en` |
| `theme_preference` | `sometimes`, `boolean` |
| `share_workout_stats` | `sometimes`, `boolean` |
| `share_body_metrics` | `sometimes`, `boolean` |
| `share_attendance` | `sometimes`, `boolean` |

# 4. Code Comments

* No inline comments (`//`) in validation rule arrays.
* JavaDoc in the top block of each `FormRequest` class explaining what operation it validates and which model it targets.
