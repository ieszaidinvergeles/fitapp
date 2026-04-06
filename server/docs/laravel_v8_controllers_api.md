# Gym — Controllers, API Architecture and Middleware

**Date:** 2026-04-06 &nbsp;|&nbsp; **Release:** `v0.8.0-controllers`

This document describes the API v1 structure, naming conventions, and 
the architectural implementation of the request handling and authorization layer.

# 1. API Architecture (The "RESTful" Standard)

The backend exposes a **RESTful API** under the `api/v1/` prefix, ensuring non-breaking 
changes for future integrations.

## 1.1. RESTful Standards

### 1.1.1. What is a RESTful API?
A standardized set of constraints (Representational State Transfer) for creating web services. All services in the GymApp are resource-based and leverage HTTP verbs (GET, POST, PUT, DELETE) to define actions, creating a predictable interface for client integration.

### 1.1.2. Statelessness and Uniformity
The API is designed to be stateless, meaning no client data is stored on the server between requests. Each call must contain all necessary data (Sanctum tokens) to be processed. Responses always use a uniform structure based on the `result` and `message` identifiers.

## 1.2. Theoretical Justification: Why v1?
*   **Versioning:** Allows for major changes in `v2` without affecting older clients.
*   **Consistency:** All endpoints follow pluralized naming (e.g., `/users`, `/gym-classes`).
*   **Namespacing:** All controllers for this version are located in `App\Http\Controllers`.

# 2. Authorization Layer and Middleware

Authorization is handled through **role-based access control (RBAC)** integrated into the 
routing middleware layer at `bootstrap/app.php`.

## 2.1. Registered Middleware Aliases

| Alias | Middleware Class | Purpose |
| :--- | :--- | :--- |
| `admin` | `AdminMiddleware` | Restricts access to super-users (`role = 'admin'`). |
| `advanced` | `AdvancedMiddleware` | Access for `admin`, `manager`, and `staff` roles. |
| `auth:sanctum`| `EnsureFrontendRequestsAreStateful` | Core Laravel authentication layer. |

> Middleware is applied at the route level in `routes/api.php`, allowing for granular 
> permission control per endpoint (e.g., users can view plans, but only admins can create them).

# 3. Request Handling (The "Thin Controller" Pattern)

All 18 controllers follow a strict, standardized implementation pattern to 
guarantee response consistency.

## 3.1. Architectural Patterns

### 3.1.1. Thin Controllers
Controllers that only handle HTTP request parsing and response formatting. In this pattern, the controller is a "mediator" that doesn't perform calculations or business logic; it merely collects input, calls its corresponding domain model, and returns the result.

### 3.1.2. Coordination and Separation
The controller acts as a conductor, delegating the "how" (logic) to the models. This separation of concerns ensures that if a gym rule changes (e.g., how the strike counter works), we only modify the model, keeping the web infrastructure untouched.

## 3.2. Standardized Response Format
Every response returns a JSON object with two mandatory keys:
*   **`result`**: Contains the requested data (Object, Array, Boolean) or `false` on failure.
*   **`message`**: A keyed array (e.g., `['general' => '...']`) for error strings or success reports.

## 3.3. Architecture: Why the try-catch block?
*   **Silence Database Errors:** Prevents internal SQL errors or traces from leaking to the client.
*   **Guaranteed Structure:** Ensures the API consumer always receives the same key structure, even when 500-level errors occur.
*   **Graceful Degradation:** Allows the controller to provide a human-readable reason for failure.

# 4. Request Sanitization and Pagination

The API enforces strict rules for data input and output formatting.

## 4.1. Sanitization Helpers
Global helpers are used before every `create` or `update` operation:
*   `limpiarCampo()`: Generic string trimming and sanitization.
*   `limpiarOrden()`: Ensures sort parameters are safe against SQL injection.
*   `limpiarNumeros()`: Casts and cleans numeric inputs for IDs or quantities.

## 4.2. Pagination Rules
All list endpoints (`index`) **must** include:
*   `paginate(10)`: Fixed chunking of 10 items per page.
*   `withQueryString()`: Persists filter parameters (like `gym_id` or `role`) during navigation.

# 5. API v1 — Route Summary

Total Routes Registered: **105** (Checked via `php artisan route:list`).

| Resource Groups | Access Level | Primary Actions |
| :--- | :--- | :--- |
| `plans`, `activities`, `recipes` | Public / Auth | View catalog. |
| `bookings`, `metrics`, `meals` | Auth (Own) | Manage personal training/nutrition. |
| `routines`, `classes`, `rooms` | Advanced | Operational gym management. |
| `users`, `gyms`, `settings` | Admin | Full control and administrative overrides. |
