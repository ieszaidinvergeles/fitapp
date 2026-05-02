# WordPress Client Migration & Theme Structure Phase

**Phase Overview**
This document outlines the architectural shift from a pure static PHP client to a dynamic structure isolated inside a WordPress theme directory. Moving static pages into an organized hierarchy lays the foundation for full WordPress integration in the client app.

## 1. Directory Structure Organization

During this phase, we have begun transitioning the monolithic `.agents/cliente/fitapp-php-cliente-wp` directory. Instead of remaining an unstructured list of static PHP files in a standalone directory, the files have been successfully migrated to the `wordpress/wordpress-theme` directory. This achieves several architectural goals:

- **Separation of Concerns:** By placing files directly into the `wordpress/wordpress-theme` folder, files correctly follow WordPress’s theme template system (e.g., `header.php`, `footer.php`, `functions.php`).
- **Encapsulation:** The frontend is treated completely independently from the Laravel Backend, communicating solely through APIs but now leveraging WP core features if necessary in the future.
- **Maintainability:** Subdirectories like `staff/` and `client/` allow clear separation depending on the platform interface.

## 2. PS1 Scripts Path Adjustments

Because of the architectural restructuing, the automation scripts (the `.ps1` files) powering development workflows needed adjustment to correctly track the new destinations:
- **`start-wp.ps1`:** The `Join-Path` reference has been modified. It previously pointed to `fitapp-php-cliente-wp`, but now points appropriately to `..\..\wordpress\wordpress-theme`, ensuring that anyone running the startup script initializes the server in the correct new location for the client interface.

## 3. Interfaces and Roles Logic Additions

We integrated specific interface rules reflecting the scope of the app:

### The Two Interfaces
The system provides two distinct visual experiences based on the user's role:
- **Employee / Staff Interface (`/staff/`):** A portal centered around management tasks. It allows staff members, assistants, and administrators to review bookings, confirm attendance, check analytics, and manage records according to their permissions.
- **Client Interface (`/client/`):** A front-facing experience solely for gym members to easily access their assigned diets, booked classes, schedule activities, view body metrics, and browse catalogs.

### The "Assistant" Role
A new administrative layer was clarified inside our logic guidelines: The **Assistant**.
This role acts as a secretary or receptionist. While an **Admin** or a **Manager** has destructive and overarching visibility powers, the **Assistant** is limited to operational day-to-day interactions. Their duties include:
- Registering members on-site.
- Consulting gym schedules and answering questions dynamically.
- Canceling or modifying non-destructive entities (like a booking or updating user profile details) under the oversight and authorization of superiors. 

## 4. "Volt GYM" Custom Post Type (CPT)

A significant aspect of moving to WordPress is utilizing its native content management structures. For this, the system incorporates the concept of the **Volt GYM Custom Post Type**.
- **What is a Custom Post Type (CPT)?** It is a WordPress feature that allows the creation of tailored content formats distinct from standard "Posts" or "Pages" to address specific domain needs.
- **Why use it?** It acts as an exclusively digital **Catalog of Products** (such as gym merchandise, sports supplements, or apparel). 
- **Scope:** At this stage, this element is strictly an informational catalog. It does not integrate an eCommerce flow (like WooCommerce checkout or payment logic). It is displayed primarily on the **Client App** interface to showcase offerings but managed on the backend by App Admins and Managers.
