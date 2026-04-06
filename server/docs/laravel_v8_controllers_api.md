# GymApp — Controllers and API Architecture (v8)

**Date:** 2026-04-06 &nbsp;|&nbsp; **Release:** `v0.8.0-controllers`

This document outlines the API structure, routing, and controller implementation for the GymApp backend.

## 1. Infrastructure and Routing
- **`bootstrap/app.php`**: Integrated API route file and registered role-based middleware aliases.
- **Middleware Aliases**:
    - `admin`: Requires `role = 'admin'`.
    - `advanced`: Requires `role` to be one of `admin`, `manager`, or `staff`.
- **`routes/api.php`**: Defined **105 routes** under the `v1` prefix, organized by access level (Public, Authenticated, Advanced, Admin).

## 2. Controller Pattern
All controllers follow a standardized implementation pattern:
- **Try-Catch Blocks**: Every database operation is wrapped to ensure JSON error responses.
- **Standardized Response**: Uses `$result` and `$messageArray` structure as defined in conventions.
- **Sanitization**: Applied `limpiarCampo`, `limpiarOrden`, and `limpiarNumeros` helpers where appropriate.
- **Pagination**: Mandatory `paginate(10)->withQueryString()` for all list endpoints.

## 3. Key Controller Features
- **BookingController**: Validates availability, status transitions, and writes to `BookingHistory`.
- **GymClassController**: Implements server-side conflict detection for rooms and instructors.
- **NotificationController**: Automates delivery log creation for all targeted recipients.
- **RoutineController**: Handles polymorphic exercise ordering and cloning.
- **UserController**: Administrative tools for blocking users and resetting strikes.

## 4. API Documentation
The API is now fully operational with all 18 entities accessible through their respective endpoints.
Total Registered Routes: **105**.
