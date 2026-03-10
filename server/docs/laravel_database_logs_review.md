# Gym — Log System Documentation v1

## 1. Why are logs necessary?

The gym application handles data that is legally sensitive and operationally critical. Without a dedicated log system, the following scenarios become impossible to resolve:

1. **Legal compliance (GDPR)**
The GDPR requires being able to demonstrate that a user consented to data processing, when they did it, from which device, and which version of the document they accepted. It also requires being able to demonstrate that a revocation was processed. Without `consent_logs` this evidence does not exist and the application is not compliant.

2. **Dispute resolution**
If a user claims they never cancelled a booking, or that a no-show was incorrectly assigned, the `bookings` table only stores the current state. Without `booking_history` it is impossible to reconstruct what happened, who changed the status, and why.

3. **Security and intrusion detection**
Without `auth_logs` there is no way to detect brute force attacks, suspicious access from unusual locations, or compromised accounts. Every login attempt — successful or failed — must be recorded with its IP and timestamp.

4. **Administrative accountability**
When a manager blocks a user, cancels a class, or changes a role, this action must be traceable. Without `admin_action_logs` there is no way to know who made a sensitive change or when.

5. **Audit trail for data modifications**
Any creation, modification or deletion of a critical entity — users, bookings, memberships — must leave a record of what the data looked like before and after. Without `audit_logs` this reconstruction is impossible once the record is modified or deleted.

6. **Notification delivery tracking**
The `notifications` table stores the notification itself but not whether it reached each recipient. Without `notification_delivery_logs` there is no way to confirm delivery, diagnose failures, or demonstrate that a user was informed of something.


## 2. Why are they not normal migrations in the same database?

The log tables share the same MySQL database as the rest of the application, but they are structurally different from the main tables for the following reasons:

### 2.1. They are immutable by design

A normal table is read, written, updated and deleted throughout the application lifecycle. Log tables only ever receive `INSERT` operations. No `UPDATE` or `DELETE` should ever be executed on them. This is not a convention — it is a legal and architectural requirement. A modifiable log is not valid as legal evidence.

### 2.2. They have no foreign key constraints

All log tables use `unsignedBigInteger` for IDs that reference entities in the main database, but declare no `FOREIGN KEY` constraints. The reason is fundamental: if a user is deleted from `users`, their bookings cascade and disappear, but the audit record of their actions must remain forever. A foreign key with `CASCADE` would destroy the log. A foreign key with `SET NULL` would corrupt it. The only correct solution is no foreign key — the relationship is logical, managed by application code, not enforced by the database engine.

### 2.3. They have no `updated_at` column

Laravel's standard `$table->timestamps()` creates both `created_at` and `updated_at`. Log tables only have `created_at` because they are never updated. Adding `updated_at` would be semantically incorrect — its presence implies the record can change.

### 2.4. They grow independently and indefinitely

Main tables reflect the current state of the business — a gym has X rooms today. Log tables accumulate every event that has ever occurred. Their growth pattern is completely different. Separating them structurally (even within the same database) makes it easier to apply different retention policies, archiving strategies, or indexing in the future without touching the main schema.

### 2.5. They live in a separate migration folder

```
database/
  migrations/
    main/       — 23 migrations for business tables
    logs/       — 6 migrations for log tables
```

Laravel 11 detects both folders automatically. This separation makes it clear at a glance which migrations belong to the business domain and which belong to observability.

---

## 3. Why do they have no relationships with the rest of the models?

The log models (`AuditLog`, `AuthLog`, `ConsentLog`, etc.) define no Eloquent relationships — no `belongsTo`, no `hasMany`, no `with()` calls pointing to main models.

**The reason is architectural, not a limitation.**

### 3.1. The data they reference may not exist

A user can be deleted. A booking can be deleted. A notification can be deleted. The log record must survive all of these events. If an Eloquent relationship were defined, calling `$auditLog->user` on a record whose user has been deleted would return `null` silently, or throw an error depending on context. The log is designed to be self-contained — the snapshot stored in `old_values` and `new_values` contains all the information needed to understand what happened, regardless of whether the original record still exists.

### 3.2. Relationships imply mutability

Defining `belongsTo(User::class)` on a log model creates an implicit assumption that the log and the user are part of the same lifecycle. They are not. The log is a historical record. The user is a live entity. Mixing their lifecycles through Eloquent relationships blurs this distinction.

### 3.3. Querying logs is done by ID, not by relationship

When the application needs to display the audit history of a user, it queries `AuditLog::where('entity_id', $userId)->where('entity_type', 'User')->get()`. This is an explicit, intentional query — not a relationship traversal. This makes it clear in the code that you are crossing a boundary between the live domain and the historical record.

---

## 4. Table conventions and naming

### 4.1. Table naming

| Table | Naming pattern |
|---|---|
| `audit_logs` | `{domain}_logs` |
| `auth_logs` | `{domain}_logs` |
| `consent_logs` | `{domain}_logs` |
| `booking_history` | `{domain}_history` — used because it describes a state timeline, not a generic event log |
| `admin_action_logs` | `{role}_{domain}_logs` |
| `notification_delivery_logs` | `{domain}_{subdomain}_logs` |

### 4.2. Column conventions

| Field | Rule | Reason |
|---|---|---|
| `*_id` references | `unsignedBigInteger`, never `foreignId()->constrained()` | No FK constraints on log tables |
| `actor_role`, `actor_id` | Always snapshot — stored as plain value at time of event | The actor's role may change after the event |
| `ip_address` | `varchar(45)` | Supports both IPv4 (15 chars) and IPv6 (39 chars) |
| `user_agent` | `text`, nullable | Free-length browser/client string, may be absent |
| `created_at` | `timestamp()->useCurrent()` — never `$table->timestamps()` | Log records are never updated, `updated_at` is semantically incorrect |
| `old_values`, `new_values`, `payload` | `json`, nullable | Structured snapshot data, cast to `array` in the model |
| `version` | `varchar(20)` | Semantic version strings like `1.0`, `2.3.1` |
| `reason` | `varchar(500)`, nullable | Controlled free text, acotado porque es generado por el sistema |
| `action` (audit) | `enum` — values are fixed and known | `created`, `updated`, `deleted` will never change |
| `action` (admin) | `varchar(80)` — free string | New admin actions must be addable without a migration |

### 4.3. Nullable rules specific to log tables

| Field | Nullable | Reason |
|---|---|---|
| `actor_id` | Yes | The system can generate audit events without an authenticated user |
| `user_id` in auth_logs | Yes | A failed login attempt may reference a non-existent email |
| `user_id` in consent_logs | No | A consent record without a user has no legal validity |
| `from_status` in booking_history | Yes | The first transition has no previous status |
| `changed_by_id` | Yes | Automatic system transitions have no human actor |
| `delivered_at`, `read_at` | Yes | These states may not have been reached yet |
| `target_entity_type`, `target_entity_id` | Yes | Some admin actions do not target a specific entity |

### 4.4. Model namespace

All log models live under `App\Models\Log\` to make the boundary explicit:

```
app/
  Models/
    User.php
    Booking.php
    ...
    Log/
      AuditLog.php
      AuthLog.php
      ConsentLog.php
      BookingHistory.php
      AdminActionLog.php
      NotificationDeliveryLog.php
```

### 4.5. Model configuration

All log models share the same base configuration:

```php
public $timestamps = false;        // Only created_at, managed manually
protected $fillable = [...];       // Explicit — no $guarded = []
protected $casts = [...];          // json fields cast to array, timestamps to datetime
```

No log model defines `$with`, `belongsTo`, `hasMany` or any other Eloquent relationship.

---

## 5. Code Comments

* No inline comments (`//`) in columns
* JavaDoc in the top block of each Migration & Class files