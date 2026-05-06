# Dashboard Endpoints — v0.11.0

**Version:** `v0.11.0-dashboards`
**Date:** 2026-04-09
**Scope:** Two aggregate controller endpoints that consolidate multiple API calls into a single request per dashboard screen.

---

## 1. Context and Purpose

Prior to this version, the frontend required between 6 and 8 individual API calls to populate a single dashboard screen (user profile, membership plan, active routine, upcoming classes, body metrics, notifications). This introduced unnecessary latency, client-side orchestration complexity, and multiple round-trips.

This version introduces two **aggregate endpoints** — one for regular users and one for staff/admin — each returning a full, pre-composed payload in a single HTTP call.

---

## 2. Design Decisions

### 2.1 Why Not GraphQL

The existing API is REST over JSON. Introducing GraphQL for two aggregate endpoints would require a new dependency (`rebing/graphql-laravel`), a schema layer, and additional learning overhead for the frontend team. The chosen approach achieves the same result within the existing architecture.

### 2.2 Eager Loading Strategy

Both controllers use `with()` and `whereHas()` to load all required relations in a single database round-trip. This avoids the N+1 problem that would arise from lazy-loading relations inside resource transformations.

### 2.3 Role-Based Scoping (Staff Dashboard)

- **Admin** → receives data for **all gyms** in the system.
- **Manager / Staff** → receives data scoped to their `current_gym_id` only.

This scoping is applied at the query level, not at the serialization level, to minimize data transfer.

---

## 3. Endpoints

### 3.1 User Dashboard

| Field | Value |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/v1/dashboard` |
| **Auth** | `auth:sanctum` |
| **Roles** | All authenticated users |

#### Response Payload

```json
{
  "result": {
    "user":                       "UserResource",
    "membership":                 "MembershipPlanResource | null",
    "gym":                        "GymResource | null",
    "active_routine":             "RoutineResource | null",
    "upcoming_bookings":          "BookingResource[] (max 5, active status, future classes)",
    "latest_metric":              "BodyMetricResource | null",
    "unread_notifications_count": "integer",
    "next_class":                 "GymClassResource | null"
  },
  "message": { "general": "OK" }
}
```

#### Field Descriptions

| Field | Description |
|---|---|
| `user` | Full user profile via `UserResource`, includes gym and plan when loaded |
| `membership` | The user's current membership plan or `null` if none assigned |
| `gym` | The gym the user is currently assigned to |
| `active_routine` | The first routine where the `user_active_routines` pivot `is_active = true` |
| `upcoming_bookings` | Up to 5 future active bookings, each including the gym class details |
| `latest_metric` | The most recent `BodyMetric` record for the user |
| `unread_notifications_count` | Count of notifications targeting the user's gym since their account creation |
| `next_class` | The next non-cancelled class scheduled at the user's gym |

---

### 3.2 Staff Dashboard

| Field | Value |
|---|---|
| **Method** | `GET` |
| **URL** | `/api/v1/staff/dashboard` |
| **Auth** | `auth:sanctum` + `advanced` middleware |
| **Roles** | `admin`, `manager`, `staff` |

#### Response Payload

```json
{
  "result": {
    "gyms":                  "GymResource[] (all gyms for admin, own gym for manager/staff)",
    "today_classes":         "GymClassResource[] (today's non-cancelled classes)",
    "today_bookings_count":  "integer",
    "active_members_count":  "integer (clients with active membership in the gym)",
    "staff_present_today":   "StaffAttendanceResource[] (clock-ins for today)",
    "pending_notifications": "NotificationResource[] (last 10, ordered newest first)",
    "low_booking_classes":   "GymClassResource[] (< 30% occupancy, next 48h)"
  },
  "message": { "general": "OK" }
}
```

#### Field Descriptions

| Field | Description |
|---|---|
| `gyms` | Gym overview with manager. Admins see all gyms; others see their assigned gym only |
| `today_classes` | All active (non-cancelled) classes scheduled for today, with activity and room |
| `today_bookings_count` | Total active bookings across today's classes |
| `active_members_count` | Users with `membership_status = active` and `role ∈ {client, user_online}` in the gym |
| `staff_present_today` | Staff attendance records where `date = today()` for the gym |
| `pending_notifications` | Last 10 notifications targeting the gym or the global audience |
| `low_booking_classes` | Non-cancelled classes in the next 48 hours with less than 30% capacity filled |

---

## 4. Controller Files

| File | Namespace |
|---|---|
| `app/Http/Controllers/DashboardController.php` | `App\Http\Controllers` |
| `app/Http/Controllers/StaffDashboardController.php` | `App\Http\Controllers` |

---

## 5. Route Registration

Added to `routes/api.php` inside the `auth:sanctum` middleware group:

```php
// Aggregate Dashboards
Route::get('/dashboard',        [DashboardController::class,      'index']);
Route::middleware('advanced')->get('/staff/dashboard', [StaffDashboardController::class, 'index']);
```

---

## 6. SOLID Compliance

| Principle | Application |
|---|---|
| **SRP** | Each controller handles one dashboard payload; formatting is delegated to Resource classes |
| **OCP** | New dashboard fields are added as additional keys without modifying the resource contracts |
| **LSP** | Both controllers extend `Controller` and honour the same response contract |
| **ISP** | Controllers only depend on the models and resources they actually use |
| **DIP** | Controllers depend on Eloquent relation abstractions, not raw DB queries or model internals |
