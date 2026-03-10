# Gym — Database and Laravel Implementation Review v3

This document describes the revised and fixed database schema conventions, migration rules,
foreign key behaviors, and the Eloquent model configuration used in the Gym App backend.
 
# Migration Conventions — Gym App

## Column Types and Lengths

| Type                         | Rule                   | Example                          |
| ---------------------------- | ---------------------- | -------------------------------- |
| `string` name                | 80 characters          | `string('name', 80)`             |
| `string` address             | 200 characters         | `string('address', 200)`         |
| `string` city                | 80 characters          | `string('city', 80)`             |
| `string` phone               | 20 characters          | `string('phone', 20)`            |
| `string` coordinates         | 100 characters         | `string('location_coords', 100)` |
| `string` image/video URL     | 500 characters         | `string('image_url', 500)`       |
| `string` email               | 150 characters         | `string('email', 150)`           |
| `string` password_hash       | 255 characters         | `string('password_hash', 255)`   |
| `string` full_name           | 150 characters         | `string('full_name', 150)`       |
| `string` dni                 | 9 characters           | `string('dni', 9)`               |
| `text`                       | No length in migration | `text('description')`            |
| `integer`                    | Never has length       | `integer('capacity')`            |
| `decimal` measurements       | `(5, 1)`               | `decimal('weight_kg', 5, 1)`     |
| `decimal` prices/percentages | `(6, 2)`               | `decimal('price', 6, 2)`         |

> `string('name', 80)` and `string('name', length: 80)` are identical in Laravel.
> `text` does not accept length in the migration — the limit goes in the FormRequest with `max:280`.
> `integer` does not accept length — the limit goes in the FormRequest with `max:`.

## Fields Converted to ENUM

| Table                | Field                     | Values                                                                                                                                                                                                                               |
| -------------------- | ------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| `membership_plans`   | `type`                    | `physical`, `online`, `duo`                                                                                                                                                                                                          |
| `activities`         | `intensity_level`         | `low`, `medium`, `high`, `extreme`                                                                                                                                                                                                   |
| `exercises`          | `target_muscle_group`     | `chest`, `upper_back`, `lower_back`, `shoulders`, `biceps`, `triceps`, `forearms`, `core`, `obliques`, `quadriceps`, `hamstrings`, `glutes`, `calves`, `hip_flexors`, `adductors`, `abductors`, `traps`, `lats`, `neck`, `full_body` |
| `users`              | `role`                    | `admin`, `manager`, `staff`, `client`, `user_online`                                                                                                                                                                                 |
| `users`              | `membership_status`       | `active`, `paused`, `expired`                                                                                                                                                                                                        |
| `bookings`           | `status`                  | `active`, `cancelled`, `attended`, `no_show`                                                                                                                                                                                         |
| `routines`           | `difficulty_level`        | `beginner`, `intermediate`, `advanced`, `expert`                                                                                                                                                                                     |
| `notifications`      | `target_audience`         | `global`, `staff_only`, `specific_gym`, `specific_user`                                                                                                                                                                              |
| `gym_inventory`      | `status`                  | `operational`, `maintenance`, `retired`                                                                                                                                                                                              |
| `user_favorites`     | `entity_type`             | `gym`, `activity`, `routine`                                                                                                                                                                                                         |
| `settings`           | `language_preference`     | `es`, `en`                                                                                                                                                                                                                           |
| `recipes`            | `type`                    | `breakfast`, `lunch`, `dinner`, `snack`, `pre_workout`, `post_workout`                                                                                                                                                               |
| `user_meal_schedule` | `meal_type`               | `breakfast`, `lunch`, `dinner`, `snack`, `pre_workout`, `post_workout`                                                                                                                                                               |
| `classes`            | `start_time` / `end_time` | —                                                                                                                                                                                                                                    |


## Nullable Rules

### Never nullable

* All `id` and `foreignId` fields that are PKs or part of a composite PK
* Record identity fields: `date`, `name`, main fields without which the record has no meaning
* Fields with `default`: booleans with default values, ENUMs with default values

### Nullable allowed

* Optional fields that the user may fill later (`height_cm`, `body_fat_pct`, etc.)
* Optional FKs (`recipe_id` in `user_meal_schedule`, `associated_diet_plan_id` in `routines`)
* URLs and profile photos

## Foreign Key Behavior

| Situation                                   | Behavior            | Example                      |
| ------------------------------------------- | ------------------- | ---------------------------- |
| The child has no meaning without the parent | `cascadeOnDelete()` | `bookings` → `users`         |
| Historical record must remain               | `nullOnDelete()`    | `staff_attendance` → `users` |
| The gym is deleted and drags its children   | `cascadeOnDelete()` | `rooms` → `gyms`             |

### Summary Table by Migration

| Table                  | FK                                        | Behavior |
| ---------------------- | ----------------------------------------- | -------- |
| `rooms`                | `gym_id`                                  | cascade  |
| `classes`              | `gym_id`                                  | cascade  |
| `classes`              | `activity_id`, `instructor_id`, `room_id` | null     |
| `bookings`             | `class_id`, `user_id`                     | cascade  |
| `body_metrics`         | `user_id`                                 | cascade  |
| `gym_inventory`        | `gym_id`, `equipment_id`                  | cascade  |
| `notifications`        | `sender_id`, `related_gym_id`             | null     |
| `routines`             | `creator_id`, `associated_diet_plan_id`   | null     |
| `routine_exercises`    | `routine_id`, `exercise_id`               | cascade  |
| `settings`             | `user_id`                                 | cascade  |
| `staff_attendance`     | `staff_id`, `gym_id`                      | null     |
| `user_active_routines` | `user_id`, `routine_id`                   | cascade  |
| `user_favorites`       | `user_id`                                 | cascade  |
| `user_meal_schedule`   | `user_id`                                 | cascade  |
| `user_meal_schedule`   | `recipe_id`                               | null     |
| `user_partners`        | `primary_user_id`, `partner_user_id`      | cascade  |
| `gyms`                 | `manager_id` (migration 000008)           | null     |
| `users`                | `current_gym_id`, `membership_plan_id`    | null     |

## Circular Dependency gyms ↔ users

`gyms.manager_id` references `users` and `users.current_gym_id` references `gyms`.
Laravel runs migrations one by one in order, so both FKs cannot be declared at the same time.

**Solution in three migrations:**

1. `000006` — Creates `gyms` with `manager_id` as `unsignedBigInteger` without FK
2. `000007` — Creates `users` with FK to `gyms` (already exists)
3. `000008` — Adds the FK of `manager_id` in `gyms` (now `users` already exists)

> `unsignedBigInteger('manager_id')` and `foreignId('manager_id')` produce the same column type.
> The difference is that `foreignId` allows chaining `->constrained()`, but since the FK
> is added in a later migration, `unsignedBigInteger` is used to leave it unconstrained.

## settings Table: Strict 1-to-1 Relationship

It omits the classic auto-increment `id` to create a pure one-to-one relationship directly tied to the `users` table.

**Main characteristics:**

1. **Shared PK and FK:** The `user_id` column is simultaneously Primary Key and Foreign Key.
2. **Integrity guarantee:** As it is a Primary Key, it is impossible to have more than one configuration for the same user.
3. **Automatic cleanup:** Includes `->cascadeOnDelete()` so when the user is deleted, their settings are deleted with them.

> By using `$table->unsignedBigInteger('user_id')->primary()`, the need for a separate `id` column is removed. This optimizes storage and structurally enforces the 1-to-1 relationship at the database engine level.


## user_favorites Table: Polymorphism and Composite PK

It works as a universal pivot table using a polymorphic pattern, avoiding the creation of individual tables (like `gym_user_favorites` or `routine_user_favorites`).

**Main characteristics:**

1. **Polymorphic structure:** Uses `entity_type` (gym, activity, routine) and `entity_id` to identify which table and record the favorite belongs to.
2. **Composite Primary Key:** Uses `['user_id', 'entity_type', 'entity_id']` to prevent a user from saving the same element as a favorite twice.
3. **Cascade deletion:** The FK of `user_id` ensures no orphan favorites remain if the user is deleted.

> The `entity_id` column is defined as `unsignedBigInteger` without an explicit Foreign Key. This is because, in a polymorphic design, relational database engines do not allow a single FK to dynamically point to different tables depending on the value of another column (`entity_type`).


## Code Comments

* No inline comments (`//`) in columns
* JavaDoc in the top block of each migration class
* ENUMs document their possible values by themselves and do not need `->comment()`


# Why Delete Pivot Table Models?

In Laravel, purely relational intermediate tables do not need their own Model class. For this reason, you can safely delete these 4 files from your `app/Models` directory:

* **`GymInventory.php`** (manages the `gym_inventory` table)
* **`RoutineExercise.php`** (manages the `routine_exercises` table)
* **`UserActiveRoutine.php`** (manages the `user_active_routines` table)
* **`UserPartner.php`** (manages the `user_partners` table)

The framework is designed to manage these tables transparently through the `belongsToMany` methods of your main models. Keeping these files only adds unnecessary complexity and clutters your code. By deleting them, you follow the framework’s standard conventions, avoid redundant code, and centralize logic in the truly important entities (such as `Gym`, `Routine`, or `User`), accessing intermediate data easily through the magic property `->pivot`.


## Foreign Keys in Eloquent: Convention vs Configuration

Laravel follows the principle of **"Convention over Configuration"**. If you name your columns following its standard, the framework automatically infers relationships. If you use custom names, you must explicitly define them.

**Why sometimes it is specified and sometimes not:**

1. **Without specifying:**
   `hasMany(StaffAttendance::class)` — By not passing a second parameter, Laravel automatically assumes that the foreign key in the related table is named exactly like the current model (lowercase) followed by `_id`. If this code is inside the `Gym` model, Laravel will automatically look for the column `gym_id` in the `staff_attendances` table.

2. **Specifying the name:**
   `hasMany(AppNotification::class, 'related_gym_id')` — Here the standard convention is broken. The foreign key in the notifications table is not called `gym_id` (the default name), but `related_gym_id`. When using a semantic or different name, it is mandatory to pass it as the second parameter so Laravel knows how to link the tables.

> The method signature in Eloquent is `$this->hasMany(Model::class, 'foreign_key', 'local_key')`.
> If you omit the last two parameters, Laravel assumes by default that the `foreign_key` is `[current_model_name]_id` and the `local_key` is simply `id`.



## Using the `$casts` Property (Type Conversion)

**Why use `$casts` in general?**

When you query your database, drivers (like MySQL or PostgreSQL) usually return all data as plain text strings (`string`), even if in the database they were booleans, numbers, or JSON fields. The `$casts` property is how we tell Laravel:

*"When you read this value from the database, convert it to this specific PHP type; and when saving it, do the reverse process."*

This provides:

* **Strict typing:** Booleans become real `true`/`false` values (not `"1"` or `"0"`).
* **Date magic:** `date` or `datetime` fields become **Carbon** objects, allowing operations like `$date->diffForHumans()`.
* **Automatic JSON handling:** Converts plain text JSON into a PHP `array` when reading, and JSON when saving.
* **Security:** The `hashed` cast automatically encrypts passwords before saving.


## Breakdown of Models, Migrations and Their Casts

Here is the exact relationship of all the main models generated from the migrations, and which type conversions (`$casts`) apply to each one:

### 1. App Core and User Models

* **`MembershipPlan`** (Migration: `000001_create_membership_plans_table`)

* `allow_partner_link => boolean`: Transforms the DB 0/1 into a real `bool`.

* `price => decimal:2`: Ensures price always has 2 decimals for financial precision.

* **`User`** (Migration: `000007_create_users_table`)

* `birth_date => date`: Converts it to a Carbon object (no time).

* `cancellation_strikes => integer`: Allows mathematical operations (`+ 1`).

* `is_blocked_from_booking => boolean`: Enables direct logical validations.

* `password_hash => hashed`: Automatically encrypts any value assigned before saving to the DB.

* **`Setting`** (Migration: `000018_create_settings_table`)

* `user_id => integer`: Ensures the PK/FK is handled numerically.

* `share_workout_stats`, `share_body_metrics`, `share_attendance`, `theme_preference` `=> boolean`: All cast to native `bool` for easier conditionals in the frontend.

### 2. Facility Models

* **`Gym`** (Migration: `000006_create_gyms_table`)

* `manager_id => integer`: Maintains numeric ID integrity for the foreign key.

* **`Room`** (Migration: `000009_create_rooms_table`)

* `gym_id`, `capacity => integer`: Ensures capacities and references are real numbers in PHP.

* **`Equipment`** (Migration: `000004_create_equipment_table`)

* `is_home_accessible => boolean`: Converts the state to a strict boolean.

### 3. Training Models

* **`Activity`** (Migration: `000002_create_activities_table`)

* *No casts:* Fields are only text or enums handled well as `string`.

* **`Exercise`** (Migration: `000005_create_exercises_table`)

* *No casts:* Text, URLs, and enums.

* **`Routine`** (Migration: `000016_create_routines_table`)

* `creator_id`, `associated_diet_plan_id`, `estimated_duration_min => integer`: Numeric conversion for FKs and durations.

* **`GymClass`** (Migration: `000010_create_classes_table`)

* `gym_id`, `activity_id`, `instructor_id`, `room_id`, `capacity_limit => integer`.

* `start_time`, `end_time => datetime`: Converts them into Carbon objects with precise time.

* `is_cancelled => boolean`: Boolean state validation.

* **`Booking`** (Migration: `000011_create_bookings_table`)

* `class_id`, `user_id => integer`.

* `booked_at`, `cancelled_at => datetime`: Converts timestamps to Carbon objects.

### 4. Nutrition and Health

* **`DietPlan`** (Migration: `000003_create_diet_plans_table`)

* *No casts:* Descriptive texts.

* **`Recipe`** (Migration: `000015_create_recipes_table`)

* `calories => integer`: Integer number.

* `macros_json => array`: **Fundamental.** Automatically converts the JSON string from the database (e.g. `{"protein": 30}`) into a PHP associative array ready to use.

* **`BodyMetric`** (Migration: `000012_create_body_metrics_table`)

* `user_id => integer`.

* `date => date`: Carbon object.

* `weight_kg`, `height_cm => decimal:1`: Cast to float/decimal with 1 precision digit.

* `body_fat_pct`, `muscle_mass_pct => decimal:2`: Cast with 2 precision digits.


### 5. Logs and Utility Models (Logs/Notifications)

* **`Notification`** (Migration: `000014_create_notifications_table`)

* `sender_id`, `related_gym_id => integer`.

* `created_at => datetime`: For relative time handling ("5 minutes ago").

* **`StaffAttendance`** (Migration: `000019_create_staff_attendance_table`)

* `staff_id`, `gym_id => integer`.

* `clock_in`, `clock_out => datetime`: Carbon objects with precise clock-in time.

* `date => date`: Carbon object containing only day, month, and year.

* **`UserMealSchedule`** (Migration: `000022_create_user_meal_schedule_table`)

* `user_id`, `recipe_id => integer`.

* `date => date`: Carbon object.

* `is_consumed => boolean`.

* **`UserFavorite`** (Migration: `000021_create_user_favorites_table`)

* `user_id`, `entity_id => integer`. *(Note: `entity_type` is an enum and remains a `string`.)*


## Fix Report: Database Migrations

To make the schema fully functional in a real MySQL environment, the following critical adjustments were made:

### 1. Consistency in `SET NULL` Constraints

* **Error:** `onDelete('set null')` was applied to columns that did not accept null values.
* **Solution:** Added the `->nullable()` method to all foreign key columns using the set-null deletion rule.
* **Affected tables:** `users` (gym_id), `notifications` (related_gym_id), `user_meal_schedule` (recipe_id), and the circular relationship in `gyms` (manager_id).

### 2. Integrity of Composite Primary Keys (Pivot Tables)

* **Error:** MySQL prohibits any column that is part of a `PRIMARY KEY` from being `nullable`.
* **Solution:** Removed the `->nullable()` attribute from IDs in pivot tables and changed deletion logic to `cascadeOnDelete()`. If a parent disappears, the pivot record is fully removed.
* **Affected tables:** `gym_inventory`, `routine_exercises`, `user_active_routines`, and all other many-to-many tables.

### 3. Circular Dependency Resolution (Gyms ↔ Users)

* **Error:** A gym needed a `manager_id` (User) while a user needed a `current_gym_id` (Gym). One of the tables always failed during migration because the other did not exist yet.
* **Solution:** 
  1. Created the `gyms` table without the FK constraint.
  2. Created the `users` table with its FK to gyms.
  3. Added an extra migration (`add_manager_foreign_key_to_gyms_table`) to inject the manager relationship once both tables existed.

### 4. Removal of Duplicate Definitions

* **Error:** Redundant use of `$table->foreignId('name')` followed by another manual definition of the same column, causing `Duplicate column name`.
* **Solution:** Standardized the flow in **2 phases** within the same `Blueprint`:
  * **Phase 1:** Column definition (`foreignId`).
  * **Phase 2:** Logical constraint definition (`foreign()->references()->on()`).

## Code Comments

* No inline comments (`//`) in columns
* JavaDoc in the top block of each migration class
