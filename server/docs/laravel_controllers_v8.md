# Gym — Controllers, API Architecture and Middleware (v8)

**Date:** 2026-04-06 &nbsp;|&nbsp; **Release:** `v0.8.0-controllers`

This document outlines the API structure, routing, and controller implementation for the GymApp backend.

# 1. API Infrastructure and Routing

## 1.1. Unified Routing and Versioning
The backend exposes a **RESTful API** under the `api/v1/` prefix, ensuring non-breaking 
changes for future integrations.
- **`routes/api.php`**: Consolidated all 105 routes under the `v1` prefix.
- **Grouping**: Routes are organized by their respective resource and access level.

## 1.2. Role-Based Access Control (RBAC)
Authorization is handled through custom middleware integrated into `bootstrap/app.php`.

| Alias | Middleware Class | Role Access |
| :--- | :--- | :--- |
| `admin` | `AdminMiddleware` | Restricts access to super-users (`role = 'admin'`). |
| `advanced` | `AdvancedMiddleware` | Access for `admin`, `manager`, and `staff` roles. |
| `auth:sanctum`| `EnsureFrontendRequestsAreStateful` | Client/User authentication layer. |

# 2. Controller Architecture

## 2.1. Request Handling Standard
All 18 controllers follow a standardized implementation pattern based on the **Thin Controller** architecture:
- **Try-Catch Blocks**: Every operation is wrapped in a catch block to prevent SQL leakage.
- **Standardized Response**: JSON output always contains `result` and `message` keys.
- **Sanitization**: Applied `limpiarCampo`, `limpiarOrden`, and `limpiarNumeros` helpers.

## 2.2. Key Controller Logic
- **BookingController**: Finalized booking creation and cancellation with 2h time check.
- **GymClassController**: Implemented real-time room and instructor vacancy checks.
- **StaffAttendanceController**: Added clock-in/out logic for employee tracking.
- **UserController**: Administrative tools for user blocking and strike management.

# 3. Infrastructure Fixes and Refinements

## 3.1. Base Controller Creation
A foundational [Controller.php](file:///c:/Users/SameAsKyndryl/Projects/Work/fitapp/server/laravel/app/Http/Controllers/Controller.php) class was added to `App\Http\Controllers` as required by the Laravel 11 structure. This provides a shared base for dependency injection and standard behaviors.

## 3.2. Response Consistency
Fixed several inconsistency issues in the response message arrays, ensuring that every endpoint provides a `general` key for high-level feedback.
- **Total Routes**: 105 active routes confirmed via `php artisan route:list`.
