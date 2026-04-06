# GymApp — Models and Business Logic (v7)

**Date:** 2026-04-02 &nbsp;|&nbsp; **Release:** `v0.7.0-models`

This document details the implementation of the 18 main Eloquent models, their relationships, and encapsulated business logic.

## 1. Foundation Models (Phase 1)
- **MembershipPlan**: Pricing precision and partner link logic.
- **Activity**: Core taxonomy for gym classes.
- **DietPlan**: Nutritional goal mapping.
- **Equipment**: Inventory categorization.
- **Exercise**: Multi-muscle group targeting and instruction URLs.
- **Recipe**: JSON macro casting and calorie calculation.

## 2. Core Identity (Phase 2)
- **Gym**: Manager assignment and location metadata.
- **User**: Role-based authorization helpers, strike management, and profile logic.

## 3. Operational Logic (Phases 3-5)
- **Room**: Scheduling conflict detection for class booking.
- **Routine**: Exercise ordering via pivot data and duplication logic.
- **GymClass**: Capacity management, occupancy tracking, and cancellation flows.
- **Booking**: Lifecycle states (`active`, `attended`, `no_show`, `cancelled`) and history logging.

## 4. User Personalization (Phases 6-8)
- **BodyMetric**: BMI calculation and variation delta tracking.
- **UserMealSchedule**: Calorie aggregation and week-scoped filtering.
- **Setting**: Privacy and UI preferences with default reset logic.
- **UserFavorite**: Polymorphic relationship to gym/activity/routine.
- **StaffAttendance**: Work hours calculation and clock-in/out tracking.
- **Notification**: Audience-based recipient resolution.

## 5. Audit and History (Phase 9)
Consolidated implementation of atomic log models in `App\Models\logs`:
- `AuditLog`, `AuthLog`, `AdminActionLog`, `BookingHistory`, `ConsentLog`, `NotificationDeliveryLog`.
