# Demo Seeders — v0.12.0

**Version:** `v0.12.0-demo-ready`
**Date:** 2026-04-20
**Scope:** Coherent demo data for MySQL, fixed credentials, and bug fixes applied before seeding.

---

## 1. Purpose

The seed data in this version is designed to produce a fully functional, predictable demo environment with a single command. All fixed users, gyms, classes, and bookings reference each other correctly so that every API endpoint — including both dashboard aggregates — returns meaningful data immediately after seeding.

---

## 2. Prerequisites

### 2.1 Environment Configuration

Ensure `.env` is configured for MySQL before seeding:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gymapp
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 2.2 Run the Seed Command

```bash
# From the laravel/ directory
php artisan migrate:fresh --seed
```

This drops all tables, re-runs all migrations, and executes `DatabaseSeeder`.

---

## 3. Fixed Demo Accounts

All accounts use the password: **`password`**

| Email | Role | Gym Assigned | Membership |
|---|---|---|---|
| `admin@fitapp.com` | `admin` | FitApp Madrid Centro | Basic Physical |
| `manager@fitapp.com` | `manager` | FitApp Madrid Centro (manager) | Basic Physical |
| `assistant@fitapp.com` | `assistant` | FitApp Madrid Centro | Reception desk / no destructive powers |
| `staff1@fitapp.com` | `staff` | FitApp Madrid Centro | — |
| `staff2@fitapp.com` | `staff` | FitApp Madrid Centro | — |
| `client1@fitapp.com` | `client` | FitApp Madrid Centro | Basic Physical (active) |
| `client2@fitapp.com` | `client` | FitApp Madrid Centro | Basic Physical (active) |
| `client3@fitapp.com` | `client` | — | expired |
| `online@fitapp.com` | `user_online` | — | active |

---

## 4. Seeder Execution Order

The `DatabaseSeeder` runs all seeders in this exact dependency order:

```
Step 1: MembershipPlanSeeder   — no dependencies
        ActivitySeeder          — no dependencies
        DietPlanSeeder          — no dependencies
        EquipmentSeeder         — no dependencies
        ExerciseSeeder          — no dependencies

Step 2: GymSeeder               — no FK to users yet (manager_id = null)
        UserSeeder              — users with no gym assigned yet

Step 3: [circular FK resolved]
        - gym1.manager_id       ← manager user
        - manager.current_gym_id ← gym1
        - assistant.current_gym_id ← gym1
        - staff1/staff2.current_gym_id ← gym1
        - client1/client2.current_gym_id ← gym1
        - client1/client2.membership_plan_id ← Basic Physical

Step 4: RoomSeeder              — depends on gyms
        GymInventorySeeder      — depends on gyms + equipment
        RecipeSeeder            — no dependencies
        RoutineSeeder           — depends on users
        RoutineExerciseSeeder   — depends on routines + exercises
        GymClassSeeder          — depends on gyms, activities, rooms, staff
        BookingSeeder           — depends on classes, users (client1, client2)
        BodyMetricSeeder        — depends on users
        AppNotificationSeeder   — depends on users, gyms
        SettingSeeder           — depends on users
        StaffAttendanceSeeder   — depends on users, gyms
        UserActiveRoutineSeeder — depends on users, routines
        UserFavoriteSeeder      — depends on users
        UserMealScheduleSeeder  — depends on users
        UserPartnerSeeder       — depends on users
```

---

## 5. Demo Class Schedule (GymClassSeeder)

Classes are created relative to `now()` at seed time, so they are always current:

| When | Time | Status |
|---|---|---|
| Today | 09:00 – 10:00 | active |
| Today | 12:00 – 13:00 | active |
| Today | 18:30 – 19:30 | active |
| Tomorrow | 09:00 – 10:00 | active |
| Tomorrow | 16:00 – 17:00 | active |
| Yesterday | 10:00 – 11:00 | active (past — for history) |
| Day after tomorrow | 11:00 – 12:00 | **cancelled** |

The above schedule is repeated for each gym (Madrid and Barcelona).

---

## 6. Demo Bookings (BookingSeeder)

Bookings tie `client1` and `client2` to the seeded classes:

| User | Class | Status |
|---|---|---|
| client1 | Today 09:00 | active |
| client1 | Today 12:00 | active |
| client1 | Tomorrow 09:00 | active |
| client1 | Yesterday 10:00 | attended |
| client2 | Tomorrow 09:00 | active |
| client2 | Yesterday 10:00 | no_show |
| client2 | Today 12:00 | cancelled |

---

## 7. Bug Fixes Applied (v0.12.0)

The following code corrections were made alongside the seeder rewrite:

### 7.1 `UserResource` — Wrong Field Names

| Field (before) | Field (after) | Reason |
|---|---|---|
| `profile_image_url` | `profile_photo_url` | Column name in `users` table is `profile_photo_url` |
| `phone` *(removed)* | *(removed)* | No `phone` column exists on the `users` table |
| *(missing)* | `username` | Required for display in dashboard and login response |

### 7.2 `AuthController@me()`

- **Before:** returned the raw `User` model as JSON (exposes all model attributes).
- **After:** loads `membershipPlan` and `currentGym` and returns `UserResource`, consistent with all other user endpoints.

### 7.3 `BookingController@store()`

- **Before:** relied on `GymClass::findOrFail()` to surface the missing class — which produces a 404 instead of a 422 when `class_id` is missing entirely.
- **After:** explicit `$request->validate(['class_id' => ['required', 'integer', 'exists:classes,id']])` runs first, returning a structured 422 validation error.

### 7.4 Boilerplate Cleanup

| Action | File |
|---|---|
| Deleted | `resources/views/welcome.blade.php` |
| Cleared | `routes/web.php` (welcome route removed, file kept with explanatory comment) |
