# Gym — Models, Controllers and Business Logic

**Date:** 2026-04-06 &nbsp;|&nbsp; **Releases:** `v0.7.0-models` & `v0.8.0-controllers`

This document serves as the definitive technical guide for the GymApp backend, consolidating 
the implementation details for the domain models, API layer, and core business logic.

# 1. Architectural Foundation

## 1.1. RESTful Standards

### 1.1.1. What is a RESTful API?
A standardized set of constraints (Representational State Transfer) for creating web services. All services in the GymApp are resource-based and leverage HTTP verbs (GET, POST, PUT, DELETE) to define actions, creating a predictable interface for client integration.

### 1.1.2. Statelessness and Uniformity
The API is designed to be stateless, meaning no client data is stored on the server between requests. Each call must contain all necessary data (Sanctum tokens) to be processed. Responses always use a uniform structure based on the `result` and `message` identifiers.

## 1.2. Architectural Patterns

### 1.2.1. Fat Models (Domain Logic)
A design pattern where Eloquent models handle data validation, relationships, and complex business calculations. In this architecture, the model is "intelligent" and knows how to manage its own state and side effects (such as updating counters or cascading deletions).

### 1.2.2. Thin Controllers (Coordination)
Controllers that only handle HTTP request parsing and response formatting. In this pattern, the controller is a "mediator" that doesn't perform calculations or business logic; it merely collects input, calls its corresponding domain model, and returns the result.

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

# 3. Request and Permission Guidelines

*   **View Access**: All catalog entities (Plans, Activities, Recipes) are publicly viewable by authenticated users. Logs and historic metrics are restricted for direct editing.
*   **User Ownership**: Clients can only edit their own `Settings`, `Favorites`, `MealSchedules`, and `BodyMetrics`.
*   **Advanced Access**: Staff and Managers can edit `GymClasses`, `Rooms`, `Routines`, and `Equipment` status.
*   **Admin Override**: Admins have full CRUD on `Users`, `Gyms`, `Plans`, and system-wide configurations.
