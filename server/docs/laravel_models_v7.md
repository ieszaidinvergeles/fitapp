# Gym — Models Implementation and Domain Logic (v7)

**Date:** 2026-04-06 &nbsp;|&nbsp; **Release:** `v0.7.0-models`

This document details the implementation of the 18 main Eloquent models and the domain logic added during 
the v0.7.0 development cycle.

# 1. Domain Entities Implementation

The following entities were fully implemented with their respective attributes, Eloquent casts, 
relationships, and business rules.

## 1.1. Core Identity and Operations
- **User**: Implemented strike management (`incrementStrike`), auto-blocking (`blockIfNeeded`), and membership expiry detection.
- **Gym**: Centralized gym-manager relationship and manager assignment logic.
- **GymClass**: Integrated capacity management (`isFull`), attendance marking, and cancellation cascading.
- **Room**: Added conflict detection logic (`hasConflict`) to prevent overlapping schedules.

## 1.2. Training and Personalization
- **Routine**: Implemented exercise synchronization through pivot tables and cloning logic (`duplicate`).
- **BodyMetric**: Automated BMI calculation and delta variation tracking between snapshots.
- **UserMealSchedule**: Calorie aggregation and consumption status tracking.
- **Notification**: Audience-based recipient resolution logic.

## 1.3. Infrastructure and Support
- **MembershipPlan**: Pricing precision and duo/online plan requirements.
- **Activity**: Foundation for HIIT/Yoga classification.
- **Equipment**: Inventory maintenance status and home-accessibility flags.
- **Exercise**: Targeted muscle group categorization and instruction URL management.
- **Recipe**: Unified macro JSON casting and calorie computation.

# 2. Relationship Architecture

| Source Model | Relationship | Target Model | Pivot Table / Foreign Key |
| :--- | :--- | :--- | :--- |
| `User` | `hasOne` | `Setting` | `user_id` (Shared PK/FK) |
| `Gym` | `belongsTo` | `User` | `manager_id` (Semantic name) |
| `Routine` | `belongsToMany` | `Exercise` | `routine_exercises` (Ordered) |
| `GymClass` | `belongsTo` | `Room` | `room_id` |

# 3. Model Fixes and Refinements

## 3.1. Immutable Logs Namespace
One of the key technical fixes was the consolidation of audit and history logs under the `App\Models\logs` namespace.
- **Affected Models**: `AuditLog`, `AuthLog`, `AdminActionLog`, `BookingHistory`, `ConsentLog`, `NotificationDeliveryLog`.
- **Reasoning**: To maintain a clear separation between actionable domain objects and immutable historical records.

## 3.2. Data Casting and Formatting
Standardized use of `$casts` to ensure strict typing for booleans, dates, and JSON objects across the entire model layer.
