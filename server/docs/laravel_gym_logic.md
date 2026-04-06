# Gym — Architectural Logic and Entity Roles

**Date:** 2026-04-06 &nbsp;|&nbsp; **Core Logic Reference**

This document describes the foundational architectural patterns and the specific business rules 
assigned to each entity in the system.

# 1. Architectural Patterns

## 1.1. Fat Models (Domain Logic)

### 1.1.1. What are Fat Models?
A design pattern where Eloquent models handle data validation, relationships, and complex business calculations. In this architecture, the model is "intelligent" and knows how to manage its own state and side effects (such as updating counters or cascading deletions).

### 1.1.2. Domain Encapsulation
The practice of hiding internal record states and requiring all interactions to be performed through dedicated model methods. This ensures that business rules (like member blocking or booking capacity) are always enforced, regardless of which controller or command triggers the action.

## 1.2. Thin Controllers (Coordination)

### 1.2.1. What are Thin Controllers?
Controllers that only handle HTTP request parsing and response formatting. In this pattern, the controller is a "mediator" that doesn't perform calculations or business logic; it merely collects input, calls its corresponding domain model, and returns the result.

### 1.2.2. Coordination and Separation
The controller acts as a conductor, delegating the "how" (logic) to the models. This separation of concerns ensures that if a gym rule changes (e.g., how the strike counter works), we only modify the model, keeping the web infrastructure untouched.

# 2. Detailed Business Logic Catalog

This catalog details the responsibilities for each entity, including visibility constraints and internal system logic.

## 2.1. Identity and Membership

| Entity | Role / Responsibility | Editable (Write) | Protected / System-Logic |
| :--- | :--- | :--- | :--- |
| **User** | System identity. Manages strikes and membership status. | Profile data, DNI, name, current gym. | `strikes`, `is_blocked`, `role`, `password`. |
| **Gym** | Physical facility. Controls room availability. | Name, coordinates, address. | `manager_id` (via assignment), ID. |
| **MembershipPlan** | Pricing and feature tier. Sets partner limits. | Price, benefits, duration. | Linked active memberships. |
| **Setting** | Personal privacy and UI preferences. | Theme, lang, metric sharing permissions. | `user_id`. |

## 2.2. Training and Operations

| Entity | Role / Responsibility | Editable (Write) | Protected / System-Logic |
| :--- | :--- | :--- | :--- |
| **Activity** | Catalog of exercise types (Yoga, HIIT). | Name, description, intensity. | None. |
| **Room** | Physical space in a gym. Tracks capacity. | Name, room-specific capacity. | `gym_id`, conflict detection. |
| **Routine** | Multi-exercise training plan. | Name, difficulty, instructions. | `duplicate()` logic, creator identity. |
| **Exercise** | Atomic movement with video instructions. | Muscle group, video/image URLs. | None. |
| **GymClass** | Scheduled instance of an activity. | Start/End time, instructor, room. | `is_cancelled`, capacity validation. |
| **Booking** | Session reservation. Links user to class. | None (Created via store). | `status`, cancellation logic (2h limit). |
| **Equipment** | Physical gym assets. | Maintenance status, home-use. | `local_gym_id`. |

## 2.3. Nutrition and Health

| Entity | Role / Responsibility | Editable (Write) | Protected / System-Logic |
| :--- | :--- | :--- | :--- |
| **DietPlan** | Nutritional objective (Bulk, Cut). | General goals, duration. | None. |
| **Recipe** | High-protein dish with macros. | Ingredients, preparation, image. | `macros_json` cast, calories. |
| **MealSchedule** | Daily food calendar. | `is_consumed` (Mark as done). | `user_id`, `date`. |
| **BodyMetric** | Physical progress snapshot. | Weight, height, fat percentage. | `bmi` calculation, delta tracking. |

## 2.4. Utilities and Logs

| Entity | Role / Responsibility | Editable (Write) | Protected / System-Logic |
| :--- | :--- | :--- | :--- |
| **UserFavorite** | Bookmark for gyms, routines, or activities. | Add/Remove entries. | Validation of entity type. |
| **StaffAttendance**| Work session tracking. | Clock-out timestamp. | `staff_id`, `gym_id`, Date. |
| **Notification** | Audience-targeted broadcasts. | Title, message, audience. | Recipient resolution logic. |
| **Logs (Audit, Auth, etc.)** | Immutable history of changes. | **None** (Read-only). | All data is immutable. |
