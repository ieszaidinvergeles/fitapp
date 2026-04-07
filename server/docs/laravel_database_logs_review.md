# Gym — Log System Documentation

**Date:** 2026-03-06 (Release: `v0.3.0-log-system`)

# 1. Why Are Logs Necessary?

The gym application handles data that is legally sensitive and operationally critical. Without a dedicated log system, the following scenarios become impossible to resolve:

## 1.1. Legal Compliance (GDPR)

The GDPR requires being able to demonstrate that a user consented to data processing, when they did it, from which device, and which version of the document they accepted. It also requires being able to demonstrate that a revocation was processed. Without `consent_logs`, this evidence does not exist and the application is not compliant.

## 1.2. Dispute Resolution

If a user claims they never cancelled a booking, or that a no-show was incorrectly assigned, the `bookings` table only stores the current state. Without `booking_history` it is impossible to reconstruct what happened, who changed the status, and why.

## 1.3. Security and Intrusion Detection

Without `auth_logs` there is no way to detect brute force attacks, suspicious access from unusual locations, or compromised accounts. Every login attempt — successful or failed — must be recorded with its IP and timestamp.

## 1.4. Administrative Accountability

When a manager blocks a user, cancels a class, or changes a role, this action must be traceable. Without `admin_action_logs` there is no way to know who made a sensitive change, when it happened, or with what justification.

## 1.5. Audit Trail for Data Modifications

Any creation, modification, or deletion of a critical entity — users, bookings, memberships — must leave a record of what the data looked like before and after the change. Without `audit_logs` this reconstruction is impossible once the record has been modified or deleted.

## 1.6. Notification Delivery Tracking

The `notifications` table stores the notification itself but not whether it reached each recipient. Without `notification_delivery_logs` there is no way to confirm delivery, diagnose failures, or demonstrate that a user was informed of something.

# 2. Why Are They Not Normal Migrations in the Same Structure?

The log tables share the same MySQL database as the rest of the application, but they are structurally different from the main tables for the following reasons.

## 2.1. They Are Immutable by Design

A normal table is read, written, updated, and deleted throughout the application lifecycle. Log tables only ever receive `INSERT` operations. No `UPDATE` or `DELETE` should ever be executed on them. This is not a convention — it is a legal and architectural requirement. A modifiable log is not valid as legal evidence and cannot be trusted as an audit trail.

## 2.2. They Have No Foreign Key Constraints

All log tables use `unsignedBigInteger` for IDs that reference entities in the main database, but declare no `FOREIGN KEY` constraints. The reason is fundamental: if a user is deleted from `users`, their bookings cascade and disappear, but the audit record of their actions must remain forever.

| Deletion behavior | Problem |
| --- | --- |
| `ON DELETE CASCADE` | Destroys the log record when the referenced entity is deleted — defeats the purpose |
| `ON DELETE SET NULL` | Corrupts the log — it no longer identifies who performed the action |
| No foreign key | Log survives independently — the relationship is logical, not enforced by the engine |

The only correct solution is no foreign key. The historical meaning of a log entry is preserved in its own field values, not by the existence of the referenced record.

## 2.3. They Have No `updated_at` Column

Laravel's standard `$table->timestamps()` creates both `created_at` and `updated_at`. Log tables only have `created_at` because they are never updated. Adding `updated_at` would be semantically incorrect — its presence implies the record can change. Log models use `public $timestamps = false` and set `created_at` manually via the `useCurrent()` column default.

## 2.4. They Grow Independently and Indefinitely

Main tables reflect the current state of the business — a gym has X rooms today. Log tables accumulate every event that has ever occurred. Their growth pattern is completely different. Separating them structurally (even within the same database) makes it easier to apply different retention policies, archiving strategies, or indexing in the future without touching the main schema.

## 2.5. They Live in a Separate Migration Folder

```
database/
  migrations/
    main/       — 23 migrations for business tables
    logs/       — 6 migrations for log tables
```

Laravel 11 detects both folders automatically when listed in `database.php`. This separation makes it clear at a glance which migrations belong to the business domain and which belong to observability.

# 3. Why Do They Have No Eloquent Relationships?

The log models (`AuditLog`, `AuthLog`, `ConsentLog`, etc.) define no Eloquent relationships — no `belongsTo`, no `hasMany`, no `with()` calls pointing to main models. This is an architectural decision, not a limitation.

## 3.1. The Data They Reference May Not Exist

A user can be deleted. A booking can be deleted. A notification can be deleted. The log record must survive all of these events. If an Eloquent relationship were defined, calling `$auditLog->user` on a record whose user has been deleted would return `null` silently, or throw an error depending on context. The log is designed to be self-contained — the snapshot stored in `old_values` and `new_values` contains all the information needed to understand what happened, regardless of whether the original record still exists.

## 3.2. Relationships Imply Mutability

Defining `belongsTo(User::class)` on a log model creates an implicit assumption that the log and the user share the same lifecycle. They do not. The log is a historical record. The user is a live entity. Mixing their lifecycles through Eloquent relationships blurs this boundary and creates incorrect expectations about what the `->user` property will return over time.

## 3.3. Querying Logs Is Done by ID, Not by Relationship Traversal

When the application needs to display the audit history of a user, it queries `AuditLog` explicitly by `entity_id` and `entity_type`. This is an intentional, explicit query — not a relationship traversal. The deliberate query makes the cross-boundary access visible in the code rather than hidden behind a method call.

# 4. Table Conventions and Naming

## 4.1. Table Naming

| Table | Naming Pattern | Reason |
| --- | --- | --- |
| `audit_logs` | `{domain}_logs` | Generic data change events |
| `auth_logs` | `{domain}_logs` | Authentication events |
| `consent_logs` | `{domain}_logs` | Legal consent events |
| `booking_history` | `{domain}_history` | State timeline, not a generic event log |
| `admin_action_logs` | `{role}_{domain}_logs` | Actor-scoped action log |
| `notification_delivery_logs` | `{domain}_{subdomain}_logs` | Delivery outcome per recipient |

> `booking_history` uses the `_history` suffix intentionally because it represents the ordered sequence of state transitions of a booking record, not a generic log of system events.

## 4.2. Column Conventions

| Field | Rule | Reason |
| --- | --- | --- |
| `*_id` references | `unsignedBigInteger`, never `foreignId()->constrained()` | No FK constraints on log tables (see section 2.2) |
| `actor_role`, `actor_id` | Always a snapshot — stored as plain value at time of event | The actor's role may change after the event was recorded |
| `ip_address` | `varchar(45)` | Supports both IPv4 (15 chars) and IPv6 (39 chars) |
| `user_agent` | `text`, nullable | Free-length browser/client string, may be absent on API calls |
| `created_at` | `timestamp()->useCurrent()` — never `$table->timestamps()` | Log records are never updated — `updated_at` is semantically incorrect |
| `old_values`, `new_values`, `payload` | `json`, nullable | Structured snapshot data, cast to `array` in the model |
| `version` | `varchar(20)` | Semantic version strings like `1.0`, `2.3.1` |
| `reason` | `varchar(500)`, nullable | Controlled free text generated by the system, bounded to avoid unbounded growth |
| `action` (audit) | `enum` — values are fixed and known | `created`, `updated`, `deleted` are permanently stable and will never change |
| `action` (admin) | `varchar(80)` — free string | New admin actions must be addable without a schema migration |

## 4.3. Nullable Rules Specific to Log Tables

| Field | Nullable | Reason |
| --- | --- | --- |
| `actor_id` | Yes | The system can generate audit events without an authenticated user (e.g., scheduled jobs) |
| `user_id` in `auth_logs` | Yes | A failed login attempt may use an email that does not correspond to any existing user |
| `user_id` in `consent_logs` | No | A consent record without a user has no legal validity |
| `from_status` in `booking_history` | Yes | The first transition has no previous status |
| `changed_by_id` | Yes | Automatic system transitions have no human actor |
| `delivered_at`, `read_at` | Yes | These states may not have been reached at the time of record insertion |
| `target_entity_type`, `target_entity_id` | Yes | Some admin actions apply globally, not to a specific entity |

## 4.4. Model Namespace

All log models live under `App\Models\logs\` to make the boundary between live domain and historical record explicit. This means any developer navigating the codebase can immediately identify whether a model they are reading represents a live entity or an immutable log.

```
app/
  Models/
    User.php
    Booking.php
    ...
    logs/
      AuditLog.php
      AuthLog.php
      ConsentLog.php
      BookingHistory.php
      AdminActionLog.php
      NotificationDeliveryLog.php
```

## 4.5. Model Configuration

| Property | Value | Reason |
| --- | --- | --- |
| `$timestamps` | `false` | Only `created_at` is used, managed via column default — no `updated_at` |
| `$fillable` | Explicit array | Mass-assignment protection — no `$guarded = []` shortcut |
| `$casts` | JSON fields → `array`, timestamps → `datetime` | Type safety before data reaches application logic |

No log model defines `$with`, `belongsTo`, `hasMany`, or any other Eloquent relationship (see section 3 for rationale).

# 5. Individual Log Table Reference

## 5.1. AuditLog

Records every creation, modification, or deletion of any critical domain entity.

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `entity_type` | `varchar(80)` | No | Model class name: `User`, `Booking`, etc. |
| `entity_id` | `unsignedBigInteger` | No | ID of the affected record |
| `action` | `enum(created, updated, deleted)` | No | Type of change |
| `old_values` | `json` | Yes | State before the change |
| `new_values` | `json` | Yes | State after the change |
| `actor_id` | `unsignedBigInteger` | Yes | User who triggered the change |
| `actor_role` | `varchar(20)` | Yes | The actor's role at time of event |
| `created_at` | `timestamp` | No | Event timestamp |

## 5.2. AuthLog

Records every authentication attempt, regardless of outcome.

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `user_id` | `unsignedBigInteger` | Yes | May be null for failed attempts with unknown email |
| `email_attempt` | `string(160)` | No | The email used, always recorded |
| `event` | `enum` | No | `login_ok`, `login_failed`, `logout`, `password_reset_requested`, `password_reset_ok` |
| `ip_address` | `string(45)` | No | IPv4 or IPv6 source address |
| `user_agent` | `text` | Yes | Client identifier string |
| `created_at` | `timestamp` | No | Event timestamp |

## 5.3. ConsentLog

Records user acceptance or revocation of legal documents (privacy policy, terms of service).

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `user_id` | `unsignedBigInteger` | No | Always present — consent without identity is meaningless |
| `document_type` | `varchar(80)` | No | E.g. `privacy_policy`, `terms_of_service` |
| `version` | `varchar(20)` | No | E.g. `1.0`, `2.3.1` |
| `action` | `enum(accepted, revoked)` | No | Whether consent was given or withdrawn |
| `ip_address` | `string(45)` | No | Origin of the consent action |
| `created_at` | `timestamp` | No | Legally binding timestamp |

## 5.4. BookingHistory

Records every status transition of a booking from creation through cancellation, attendance, or no-show.

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `booking_id` | `unsignedBigInteger` | No | The booking this transition belongs to |
| `class_id` | `unsignedBigInteger` | No | Snapshot — survives booking deletion |
| `user_id` | `unsignedBigInteger` | No | Snapshot — survives user deletion |
| `from_status` | `varchar(20)` | Yes | Null on the first transition (no previous state) |
| `to_status` | `varchar(20)` | No | The new status after this transition |
| `changed_by_id` | `unsignedBigInteger` | Yes | Null for automatic system transitions |
| `reason` | `varchar(500)` | Yes | Human-readable explanation of the change |
| `created_at` | `timestamp` | No | Transition timestamp |

## 5.5. AdminActionLog

Records every sensitive administrative action performed by a manager or admin.

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `actor_id` | `unsignedBigInteger` | No | The admin or manager who performed the action |
| `actor_role` | `varchar(20)` | No | Snapshot of their role at the time |
| `action` | `varchar(80)` | No | Free string — e.g. `block_user`, `cancel_class` |
| `target_entity_type` | `varchar(80)` | Yes | Model class of the affected record, if applicable |
| `target_entity_id` | `unsignedBigInteger` | Yes | ID of the affected record, if applicable |
| `reason` | `varchar(500)` | Yes | Justification provided by the actor |
| `context_gym_id` | `unsignedBigInteger` | Yes | The gym where the action took place |
| `created_at` | `timestamp` | No | Action timestamp |

## 5.6. NotificationDeliveryLog

Records the delivery status of each notification sent to each individual recipient.

| Field | Type | Nullable | Purpose |
| --- | --- | --- | --- |
| `id` | PK | No | — |
| `notification_id` | `unsignedBigInteger` | No | The parent notification |
| `recipient_id` | `unsignedBigInteger` | No | The intended recipient |
| `delivered_at` | `timestamp` | Yes | Null until delivery is confirmed |
| `read_at` | `timestamp` | Yes | Null until the user opens the notification |
| `created_at` | `timestamp` | No | When the delivery attempt was initiated |

# 6. Code Comments

* No inline comments (`//`) in migration columns.
* JavaDoc in the top block of each migration class and each model file.