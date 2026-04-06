# Gym — Models, Relationships and Business Logic

**Date:** 2026-04-02 &nbsp;|&nbsp; **Release:** `v0.7.0-models`

This document describes the implementation of the 18 main Eloquent models, their internal logic,
relationship mapping, and the architectural decisions regarding domain encapsulation.

# 1. Model Encapsulation and SRP

The backend follows the **Fat Model, Thin Controller** pattern. Each Eloquent model is responsible
for its own data integrity and business rules.

## 1.1. Theoretical Justification: Why Logic in Models?
*   **Centralization:** Rules (like "when a booking is cancelled, increment strikes") are defined once.
*   **Reuse:** Logic is available in Controllers, Jobs, and CLI commands without replication.
*   **Testability:** Unit tests can verify business logic by interacting directly with the model state.

# 2. Relationship Architecture

Laravel's **"Convention over Configuration"** is applied across the relationship graph.

| Source Model | Relationship | Target Model | Pivot Table / Foreign Key |
| :--- | :--- | :--- | :--- |
| `User` | `hasOne` | `Setting` | `user_id` (Shared PK/FK) |
| `User` | `hasMany` | `Booking` | `user_id` |
| `User` | `belongsToMany` | `Routine` | `user_active_routines` |
| `Gym` | `hasMany` | `Room` | `gym_id` |
| `Gym` | `belongsTo` | `User` | `manager_id` (Semantic name) |
| `Routine` | `belongsToMany` | `Exercise` | `routine_exercises` (Ordered) |
| `GymClass` | `hasMany` | `Booking` | `class_id` |
| `GymClass` | `belongsTo` | `Room` | `room_id` |

> Relationships use `withPivot` for auxiliary data (e.g., `quantity` in inventory, `is_active` in user routines) to keep the domain model rich and functional.

# 3. Model-Level Business Logic

Beyond basic CRUD, models encapsulate complex operations via dedicated methods.

## 3.1. Core Service Methods

| Model | Method | Responsibility |
| :--- | :--- | :--- |
| `User` | `incrementStrike()` | Updates counter and triggers `blockIfNeeded()`. |
| `GymClass` | `cancel()` | Cascades cancellation to all active `Bookings`. |
| `GymClass` | `isFull()` | Computes availability against `capacity_limit`. |
| `Booking` | `markNoShow()` | Transitions status and penalties the user. |
| `Routine` | `duplicate()` | Performs a deep clone of the routine and its exercise pivot. |
| `BodyMetric`| `bmi()` | Derived calculation from height/weight snapshots. |
| `Notification`| `resolveRecipients()`| Logic to filter users by `target_audience`. |

# 4. Immutable Log Models

All history and audit entities are consolidated under the `App\Models\logs` namespace. 

## 4.1. Design Decisions
*   **No Relationships:** Log entries store plain IDs and snapshots (JSON). This prevents logs from breaking if a user or gym is hard-deleted.
*   **Read-Only Nature:** Log models do not use `updated_at`. Only the `created_at` timestamp is relevant for the timeline event.
*   **Snapshots:** `AuditLog` and `AdminActionLog` use JSON casts to store "before and after" states of entities.

# 5. Code Documentation Standards

Each model includes a **JSR-305/JavaDoc** block defining:
*   **SRP Statement:** Clear definition of the model's responsibility.
*   **Properties:** Accurate `@property` declarations for IDE autocompletion and static analysis.
*   **Method Scopes:** Documentation for all custom scopes and business methods.
