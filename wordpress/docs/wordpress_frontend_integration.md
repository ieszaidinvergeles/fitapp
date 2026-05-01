# WordPress Frontend Integration Guide

This is the main handoff guide for the WordPress frontend team. It maps the current theme pages in `wordpress/wordpress-theme/` to the Laravel API endpoints in `server/laravel/routes/api.php`, shows which role should access each screen, and highlights the gaps that still need care during final integration.

## Source of Truth

- Theme helpers and HTTP wrapper: `wordpress/wordpress-theme/functions.php`
- Backend routes: `server/laravel/routes/api.php`
- Frontend PHPUnit QA: `server/laravel/tests/Unit/Frontend`

The theme uses `API_BASE = http://127.0.0.1:8000/api/v1` and all API access should go through:

- `api_get()`
- `api_post()`
- `api_put()`
- `api_delete()`

These wrappers already add the Bearer token from `$_SESSION['token']` when `auth: true` is passed.

## Role Rules

The current frontend role helpers behave like this:

- `client` and `user_online`: client area
- `staff`, `assistant`, `manager`, `admin`: staff portal
- `assistant`, `manager`, `admin`: member management allowed

Current helper functions to reuse:

- `require_login()`
- `require_advanced()`
- `require_user_management()`
- `require_admin()`
- `is_advanced()`
- `can_manage_members()`
- `get_role_home_path()`

## Auth Screens

| Theme page | Current behavior | Endpoint(s) | Auth expectation |
|---|---|---|---|
| `front-page.php` | Login form for user/staff tabs | `POST /auth/login` | Public |
| `page-register.php` | Registration form | `POST /auth/register` | Public |
| `page-forgot-password.php` | Forgot password form | `POST /auth/forgot-password` | Public |
| `page-logout.php` | Clears local PHP session and redirects | None today | Authenticated user |

### Important auth note

`page-logout.php` does not currently call `POST /auth/logout`. It only destroys the local session. If the final WordPress client must invalidate the Sanctum token server-side too, this page should be updated to call `POST /auth/logout` before destroying the local session.

## Client Area Mapping

| Theme page | Role | Endpoint(s) used now | Notes |
|---|---|---|---|
| `page-client-dashboard.php` | Any logged-in client-facing user | `GET /dashboard` | Main member aggregate screen |
| `page-client-classes.php` | Logged-in client-facing user | `GET /classes`, `POST /bookings` | Supports optional `?date=` filter in the page querystring |
| `page-client-bookings.php` | Logged-in client-facing user | `GET /bookings`, `POST /bookings/{id}/cancel` | Lists user bookings and allows cancel when status is `active` |
| `page-client-routines.php` | Logged-in client-facing user | `GET /routines`, `POST /routines/{id}/activate` | Routine list plus activation |
| `page-client-routine.php` | Logged-in client-facing user | `GET /routines/{id}`, `POST /routines/{id}/activate` | Detail view for one routine |
| `page-client-metrics.php` | Logged-in client-facing user | `GET /body-metrics`, `POST /body-metrics` | Self-service body metrics |
| `page-client-meal-schedule.php` | Logged-in client-facing user | `GET /meal-schedule`, `POST /meal-schedule`, `PUT /meal-schedule/{id}`, `GET /recipes` | Uses recipes as optional meal source |
| `page-client-recipes.php` | Logged-in client-facing user | `GET /recipes` | Read-only recipe listing |
| `page-client-exercises.php` | Logged-in client-facing user | `GET /exercises` | Read-only exercise listing |
| `page-client-settings.php` | Logged-in client-facing user | `GET /settings`, `PUT /settings` | Preferences form |
| `page-client-diet-plans.php` | Logged-in client-facing user | `GET /membership-plans` | Screen title says diet plans, but the data source is membership plans |

### Client-side integration caution

`page-client-diet-plans.php` currently consumes `GET /membership-plans`, not `GET /diet-plans`. If the intended UX is truly membership plans, the page title should be renamed. If the intended UX is actual nutrition plans, the screen should be switched to `GET /diet-plans`, but that route is currently limited by backend permissions.

## Staff Portal Mapping

| Theme page | Role | Endpoint(s) used now | Notes |
|---|---|---|---|
| `page-staff-dashboard.php` | `staff`, `assistant`, `manager`, `admin` | `GET /staff/dashboard` | Main staff aggregate screen |
| `page-staff-attendance.php` | `staff`, `assistant`, `manager`, `admin` | `POST /attendance/clock-in`, `GET /attendance` | Self-service attendance tracking |
| `page-staff-manage-classes.php` | `staff`, `assistant`, `manager`, `admin` | `GET /classes` | Read/list only in current WP screen |
| `page-staff-manage-routines.php` | `staff`, `assistant`, `manager`, `admin` | `GET /routines` | Read/list only in current WP screen |
| `page-staff-rooms.php` | `staff`, `assistant`, `manager`, `admin` | `GET /rooms` | Read/list only in current WP screen |
| `page-staff-notifications.php` | `staff`, `assistant`, `manager`, `admin` | `GET /notifications` | Staff portal notification list |
| `page-staff-admin-users.php` | `assistant`, `manager`, `admin` | `GET /users` | Member management list; guarded with `require_user_management()` |

## Backend Endpoints Still Not Wired in the Theme

These routes exist in Laravel but are not yet used in the current WordPress templates.

### Authenticated general

- `POST /auth/logout`
- `GET /auth/me`
- `POST /auth/email/resend`
- `GET /auth/email/verify/{id}/{hash}`
- `GET /bookings/{id}`
- `GET /body-metrics/{id}`
- `DELETE /meal-schedule/{id}`
- `GET /favorites`
- `POST /favorites`
- `DELETE /favorites/{id}`
- `POST /attendance/{id}/clock-out`
- `GET /attendance/{id}`
- `GET /users/{id}`
- `POST /users`
- `PUT /users/{id}`

### Advanced and admin write operations

- `GET /diet-plans`
- `GET /diet-plans/{id}`
- `POST /exercises`
- `PUT /exercises/{id}`
- `POST /recipes`
- `PUT /recipes/{id}`
- `POST /routines`
- `PUT /routines/{id}`
- `POST /routines/{id}/exercises`
- `DELETE /routines/{routineId}/exercises/{exerciseId}`
- `POST /routines/{id}/reorder`
- `POST /routines/{id}/duplicate`
- `POST /classes`
- `PUT /classes/{id}`
- `POST /classes/{id}/mark-attendance`
- `POST /rooms`
- `PUT /rooms/{id}`
- `GET /notifications/{id}`
- `POST /notifications`

### Admin only

- `DELETE /bookings/{id}`
- `DELETE /body-metrics/{id}`
- `POST /membership-plans`
- `PUT /membership-plans/{id}`
- `DELETE /membership-plans/{id}`
- `POST /activities`
- `PUT /activities/{id}`
- `DELETE /activities/{id}`
- `POST /equipment`
- `PUT /equipment/{id}`
- `DELETE /equipment/{id}`
- `DELETE /exercises/{id}`
- `DELETE /recipes/{id}`
- `POST /diet-plans`
- `PUT /diet-plans/{id}`
- `DELETE /diet-plans/{id}`
- `POST /gyms`
- `PUT /gyms/{id}`
- `DELETE /gyms/{id}`
- `POST /gyms/{id}/assign-manager`
- `DELETE /users/{id}`
- `POST /users/{id}/block`
- `POST /users/{id}/unblock`
- `POST /users/{id}/reset-strikes`
- `DELETE /rooms/{id}`
- `DELETE /routines/{id}`
- `POST /classes/{id}/cancel`
- `DELETE /classes/{id}`
- `DELETE /attendance/{id}`
- `DELETE /notifications/{id}`

## Integration Checklist for the WordPress Team

- Use `functions.php` wrappers only; do not add raw cURL calls inside page templates.
- Keep role guards aligned with backend middleware.
- Reuse `api_message()` for backend error normalization.
- Keep pagination querystrings in the same style already used by the theme.
- When wiring a new screen, verify both the allowed role and the HTTP verb in `server/laravel/routes/api.php`.
- Prefer matching screen titles to the real backend resource name to avoid confusing frontend and QA.

## Recommended Next Frontend Tasks

1. Update logout to call `POST /auth/logout`.
2. Decide whether `page-client-diet-plans.php` is actually a membership-plan screen or should be rebuilt around `diet-plans`.
3. Add create/update flows in staff screens for users, classes, rooms, routines, and notifications where the backend already supports them.
4. Add detail views where only list views exist today.
5. Re-run `php vendor/bin/phpunit --testsuite FrontendQA` after each endpoint remap.
