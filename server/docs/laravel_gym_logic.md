# FitApp: Gym Logic & Role Management

This document provides a natural language overview of the business logic, user roles, application interfaces, and specific data structures like Custom Post Types (CPT) used within the FitApp platform.

## Application Interfaces

The platform is divided into two main interfaces, each tailored to a specific target audience to avoid UI clutter and improve security:

1. **Client Interface (App / Frontend):** Designed for end-users (Gym Members). Focuses on booking classes, viewing routines, metrics, meal schedules, and accessing the product catalog.
2. **Staff Interface (Dashboard / Backend):** Designed for employees. Focuses on attendance, user management, scheduling, and overall gym administration.

---

## User Roles and Permissions Matrix

The system utilizes a hierarchical Role-Based Access Control (RBAC) model.

*Term Definition:* **RBAC (Role-Based Access Control)** is a method of restricting network or system access based on the roles of individual users within an enterprise.

| Role | App Interface | Description & Capabilities | Destructive Permissions |
| :--- | :--- | :--- | :--- |
| **Admin** | Staff | Total control over the platform. Can manage other staff members, alter core settings, and delete any record. | **High** |
| **Manager** | Staff | Oversees daily gym operations. Manages schedules, routines, rooms, and notifications. Cannot alter core system users (Admin users). | **Medium** |
| **Assistant** | Staff | *Receptionist / Secretary role.* Sits between regular staff and management. Responsible for registering new members, querying client data, and cancelling classes (only when authorized by superiors). Can view a wide range of administrative data but lacks the destructive permissions of a Manager. | **Low** |
| **Staff** | Staff | Trainers and instructors. Focuses solely on operational tasks like marking attendance, viewing assigned classes, and checking their personal dashboard. | **None** |
| **Client** | Client | Physical gym members. Can access full physical gym features (class bookings, gym entry, routines). | **None** |
| **User Online**| Client | Digital-only members. Uses the same Client interface but is restricted from booking physical rooms or physical attendance. | **None** |

### Deep Dive: The `Assistant` Role

The `assistant` role was introduced to handle front-desk operations without exposing sensitive structural functions to receptionists.

*   **Why it was used:** A standard `staff` role doesn't have enough permissions to register new users or cancel bookings globally, but a `manager` role has too much power (e.g., deleting schedules entirely). The `assistant` bridges this gap, providing read-heavy access with limited, specific write access (like client onboarding).
*   **Database Note:** Ensure this user is explicitly seeded in the SQL database during local setup and WordPress initialization.

---

## Content Structures & Custom Post Types

To integrate seamlessly with WordPress on the frontend, certain data is handled as a Custom Post Type.

*Term Definition:* **Custom Post Type (CPT)** is a WordPress feature that allows developers to create custom data structures beyond the default "Posts" and "Pages". It acts like a new table or collection for specific content.

### CPT: `Volt GYM`

*   **Nature:** It serves as a visual **product catalog**.
*   **Purpose:** To showcase gym products, supplements, or merchandise directly to clients. 
*   **Current Limitations:** It acts strictly as a catalog. There is **no e-commerce checkout integrated** at this phase. 
*   **Target Audience:** It is exclusively designed to be consumed by the Client App interface, allowing members to browse offerings without making direct digital purchases.

---

## Internal Screens

### Client Screens
*   `Dashboard`: Overview of upcoming classes and daily metrics.
*   `Classes & Bookings`: Interfaces for scheduling physical attendance.
*   `Routines & Exercises`: Details on workout plans.
*   `Metrics, Meal Schedule & Recipes`: Nutrition and tracking interfaces.
*   `Settings`: User profile configuration.

### Staff Screens
*   `Dashboard`: Operational overview.
*   `Attendance`: Front-desk check-in interface.
*   `Manage Classes & Routines`: Creation and scheduling of gym offerings.
*   `Rooms & Notifications`: Logistics and broadcast messaging.
*   `Admin Users`: (Admin only) Platform user management.