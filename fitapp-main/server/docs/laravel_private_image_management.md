# Private Image Management — Architecture, Implementation and Infrastructure

**Date:** 2026-04-30 &nbsp;|&nbsp; **Release:** `v0.4.0-private-image-management`

---

# 1. Motivation and Legal Context

## 1.1. Why Private Storage?

User profile photos and gym media contain **personal data** as defined under the EU General Data Protection Regulation (GDPR) and Spain's Ley Orgánica de Protección de Datos (LOPD-GDD). Storing them in a publicly accessible directory (e.g. `public/storage/`) and serving them directly via Nginx would expose them to:

- Unauthenticated enumeration (anyone guessing IDs can download photos)
- Search engine indexing of personal images
- Inability to enforce per-user access control at the file level

**Solution:** All uploaded images are stored in `storage/app/private/` — a directory that is never mounted into Nginx and is explicitly denied in the Nginx configuration. Every image request passes through an authenticated Laravel controller endpoint that:

1. Validates the Bearer token (Sanctum).
2. Streams the file with `Cache-Control: private` headers.
3. Returns 404 if the file does not exist.

This architecture satisfies GDPR Article 25 (*data protection by design and by default*) and Article 32 (*security of processing*).

---

# 2. Architecture Overview

```
Client (app / WordPress)
       │
       │  GET /api/v1/exercises/42/image
       │  Authorization: Bearer <sanctum_token>
       ▼
   ┌────────┐     ┌────────────────────────────────────────┐
   │  Nginx │────▶│  PHP-FPM (Laravel container)           │
   └────────┘     │                                        │
                  │  ExerciseController::showImage(42)     │
                  │    │                                   │
                  │    ▼                                   │
                  │  ImageService::stream(path)            │
                  │    │                                   │
                  │    ▼                                   │
                  │  Storage::disk('private')->file()      │
                  │    │                                   │
                  │    ▼                                   │
                  │  storage/app/private/images/           │
                  │    exercises/42.jpg  ← Docker volume  │
                  └────────────────────────────────────────┘
```

**Key principle:** Nginx never has access to `storage/app/private`. The Nginx volume mount for the Laravel container only includes the `public/` directory as the document root.

---

# 3. Entities With Image Support

The following 10 entities have been updated to store, stream, upload, and delete images through authenticated API routes.

| Entity | DB Column | Storage Folder | Route Name |
|---|---|---|---|
| `User` | `profile_photo_url` | `users/` | `users.photo` |
| `Exercise` | `image_url` | `exercises/` | `exercises.image` |
| `Equipment` | `image_url` | `equipment/` | `equipment.image` |
| `Recipe` | `image_url` | `recipes/` | `recipes.image` |
| `Routine` | `cover_image_url` | `routines/` | `routines.image` |
| `DietPlan` | `cover_image_url` | `diet_plans/` | `diet-plans.image` |
| `Room` | `image_url` | `rooms/` | `rooms.image` |
| `Activity` | `image_url` | `activities/` | `activities.image` |
| `MembershipPlan` | `badge_image_url` | `membership_plans/` | `membership-plans.image` |
| `Gym` | `logo_url` | `gyms/` | `gyms.logo` |

All columns are `nullable VARCHAR(500)` storing a relative path from the disk root (e.g. `images/exercises/42.jpg`).

---

# 4. Database Migrations

## 4.1. Idempotent ALTER Migration

**File:** `database/migrations/main/2026_04_30_000001_add_image_columns_to_entities.php`

A single ALTER migration guards each `addColumn` with `Schema::hasColumn()` to ensure idempotency — safe to run against both existing installations and fresh ones.

```php
if (!Schema::hasColumn('exercises', 'image_url')) {
    $table->string('image_url', 500)->nullable()->after('video_url');
}
```

## 4.2. Entities and Columns Added

| Table | Column Added | Position |
|---|---|---|
| `users` | `profile_photo_url VARCHAR(500)` | after `dni` |
| `exercises` | `image_url VARCHAR(500)` | after `video_url` |
| `equipment` | `image_url VARCHAR(500)` | after `description` |
| `recipes` | `image_url VARCHAR(500)` | after `type` |
| `routines` | `cover_image_url VARCHAR(500)` | after `associated_diet_plan_id` |
| `diet_plans` | `cover_image_url VARCHAR(500)` | after `goal_description` |
| `rooms` | `image_url VARCHAR(500)` | after `capacity` |
| `activities` | `image_url VARCHAR(500)` | after `intensity_level` |
| `membership_plans` | `badge_image_url VARCHAR(500)` | after `price` |
| `gyms` | `logo_url VARCHAR(500)` | after `phone` |

## 4.3. CREATE Migration Sync

All base `CREATE` migrations have been updated to include the image columns, ensuring a `php artisan migrate:fresh` from scratch also produces a schema with image support. The `gym.sql` dump used for Docker MySQL initialization has also been updated.

---

# 5. Service Layer (ImageServiceInterface / ImageService)

## 5.1. Design Pattern

The image file I/O logic is encapsulated behind an interface following **DIP (Dependency Inversion Principle)**:

```
App\Contracts\ImageServiceInterface   ← abstraction
        ▲
App\Services\ImageService             ← concrete implementation (local disk)
```

This allows a future swap to S3, Cloudflare R2, or Cloudinary by creating a new implementation without touching any controller.

**Binding:** `AppServiceProvider::register()` binds the interface to the concrete class.

## 5.2. Interface Methods

| Method | Description |
|---|---|
| `upload(UploadedFile, folder, entityId): string` | Stores a new image, returns the relative path |
| `replace(UploadedFile, folder, entityId, ?oldPath): string` | Deletes the old file then uploads the new one |
| `delete(?string): bool` | Deletes a file; null-safe, no-op if not found |
| `stream(string): Response` | Reads file from disk, returns HTTP response with `Cache-Control: private` |
| `exists(?string): bool` | Checks whether a file exists in private storage |

## 5.3. Storage Path Convention

Images are stored at:

```
storage/app/private/images/{folder}/{entityId}.{extension}
```

Examples:
- `images/users/17.jpg`
- `images/exercises/5.webp`
- `images/gyms/1.png`

Using the entity ID as filename guarantees uniqueness per entity and enables deterministic replacement without needing a lookup.

## 5.4. Laravel Filesystem Disk

**Config file:** `config/filesystems.php`

```php
'private' => [
    'driver'     => 'local',
    'root'       => storage_path('app/private'),
    'visibility' => 'private',
    'throw'      => false,
],
```

> The `links` array is empty (`'links' => []`) — `php artisan storage:link` is intentionally NOT called in this project. Running it would create a symlink from `public/storage` to `storage/app/public`, which is unnecessary and potentially dangerous.

---

# 6. Controller Pattern

## 6.1. Constructor Injection (DIP)

All 10 controllers that manage images receive `ImageServiceInterface` via constructor injection:

```php
public function __construct(ImageServiceInterface $imageService)
{
    $this->imageService = $imageService;
}
```

The service container automatically resolves the concrete `ImageService` at runtime.

## 6.2. Image Lifecycle in store() / update()

The pattern is identical across all controllers:

```php
// store() — create record first, then attach image
$entity = Entity::create($request->safe()->except('image'));

if ($request->hasFile('image')) {
    $path = $this->imageService->upload(
        $request->file('image'), self::IMAGE_FOLDER, $entity->id
    );
    $entity->update(['image_url' => $path]);
}

return new EntityResource($entity->fresh());
```

```php
// update() — update fields, then replace image if a new one was sent
$entity->update($request->safe()->except('image'));

if ($request->hasFile('image')) {
    $path = $this->imageService->replace(
        $request->file('image'), self::IMAGE_FOLDER, $entity->id, $entity->image_url
    );
    $entity->update(['image_url' => $path]);
}
```

## 6.3. Endpoints Per Entity

Each entity exposes 3 dedicated image endpoints in addition to the standard CRUD:

| Method | Route | Action | Auth |
|---|---|---|---|
| `GET` | `/api/v1/{entity}/{id}/image` | `showImage` | auth:sanctum |
| `POST` | `/api/v1/{entity}/{id}/image` | `uploadImage` | advanced / admin |
| `DELETE` | `/api/v1/{entity}/{id}/image` | `deleteImage` | admin |

> **Gym** uses `logo` instead of `image`: `/gyms/{id}/logo`.  
> **User** uses `photo`: `/users/{id}/photo`.

---

# 7. Anti-Impersonation Guard (UserController)

## 7.1. Problem

Staff members' profile photos are especially sensitive because an employee could theoretically upload a photo of a colleague or celebrity to impersonate them in the system. A simple "users can edit their own profile" rule is insufficient.

## 7.2. Rules Matrix

| Actor Role | Can Change Own Photo | Can Change Others' Photo |
|---|---|---|
| `user_online` | Yes | No (403) |
| `staff` | **No (403)** | No (403) |
| `assistant` | No | Yes (except admin/manager) |
| `manager` | No | Yes (except admin/manager) |
| `admin` | Yes | Yes (anyone) |

**Key rule for `staff`:** Physical staff members **cannot** upload their own photo. The change must come from an `assistant`, `manager`, or `admin`. This prevents a staff member from replacing their photo with an impersonated image.

## 7.3. Implementation

The private method `guardPhotoUpload(User $actor, User $target)` in `UserController` enforces these rules and throws `HttpException(403)` with descriptive messages on violations.

---

# 8. FormRequest Validation

All `StoreXxxRequest` and `UpdateXxxRequest` files have been updated to include:

```php
'image' => 'nullable|image|mimes:jpeg,png,webp,gif|max:2048',
```

- `nullable`: image upload is always optional (entity can exist without an image)
- `image`: validates that the uploaded file is a valid image
- `mimes:jpeg,png,webp,gif`: restricts to safe image formats
- `max:2048`: limits file size to 2 MB

> **GymController** uses `logo` as the field name instead of `image`.  
> **Update requests** additionally include `sometimes` prefix on the image rule.

---

# 9. API Resources

All 10 `JsonResource` classes now generate the image URL dynamically as a named route rather than exposing the raw file path:

```php
'image_url' => $this->image_url
    ? route('exercises.image', ['id' => $this->id])
    : null,
```

**Why named routes instead of raw paths?**

- The raw path (e.g. `images/exercises/42.jpg`) is meaningless to the client and leaks internal directory structure.
- The authenticated route URL (e.g. `/api/v1/exercises/42/image`) is the correct interface: the client fetches it with its Bearer token and receives the binary stream.
- Changing the internal storage path in the future does not break API clients.

---

# 10. Infrastructure

## 10.1. Docker Volume for Image Persistence

A named Docker volume `voltgym_laravel_images` is mounted at `storage/app/private/images` in the `laravel`, `queue`, and `scheduler` services.

**Why a named volume and not a bind mount?**

| Criterion | Named Volume | Bind Mount |
|---|---|---|
| Performance (Linux) | Better (kernel managed) | Slower |
| Portability | Full (no host path) | Requires same path on all hosts |
| Persistence on rebuild | Yes (`docker compose up --build`) | Yes |
| Azure / cloud compatible | Yes | Host-dependent |
| Backups | `docker volume backup` | Standard file backup |

> **Critical:** The `voltgym_laravel_images` volume must never be deleted without backing up its contents first. Use `docker volume inspect voltgym_laravel_images` to find the physical path on the host.

## 10.2. Nginx Deny Rules (Defense-in-Depth)

The private image volume is **not mounted in the Nginx container**. As a second layer of defense, the Nginx Laravel server block also explicitly denies `/storage` and `/storage/app/private` with HTTP 403:

```nginx
location ^~ /storage/app/private {
    deny all;
    return 403;
}

location ^~ /storage {
    deny all;
    return 403;
}
```

This prevents any future misconfiguration (e.g. accidental volume mount) from exposing private files.

## 10.3. Entrypoint Directory Bootstrap

`entrypoint.sh` now creates all required subdirectories inside the named volume on every container start:

```bash
PRIVATE_IMAGES_BASE="storage/app/private/images"
for DIR in users exercises equipment recipes routines diet_plans rooms \
           activities membership_plans gyms; do
    mkdir -p "${PRIVATE_IMAGES_BASE}/${DIR}"
done
chmod -R 775 storage/app/private
chown -R www-data:www-data storage/app/private
```

This is idempotent — safe to run repeatedly even if directories already exist.

## 10.4. Upload Size Limit

`client_max_body_size` in the Nginx Laravel server block was set to **10 MB** (previously 64 MB). This is sufficient for image uploads (max 2 MB enforced at the FormRequest level) while reducing attack surface.

---

# 11. File Access Flow (End-to-End)

The following sequence describes the complete request lifecycle for `GET /api/v1/exercises/42/image`:

```
1. Client sends GET /api/v1/exercises/42/image
   Authorization: Bearer <token>

2. Nginx receives request on port 8000
   → Matches location / → passes to FastCGI (laravel:9000)
   → location ^~ /storage rules do NOT match (URL starts with /api/)

3. PHP-FPM receives request
   → Sanctum middleware validates Bearer token
   → Routes to ExerciseController::showImage(42)

4. ExerciseController
   → Exercise::findOrFail(42) — verifies record exists
   → Checks $exercise->image_url is not null
   → Calls $this->imageService->stream($exercise->image_url)

5. ImageService::stream()
   → Storage::disk('private')->exists('images/exercises/42.jpg')
   → Storage::disk('private')->path() → absolute path on Docker volume
   → response()->file($path, ['Cache-Control' => 'private, max-age=3600'])

6. PHP-FPM sends binary response back through Nginx to the client
   Content-Type: image/jpeg
   Cache-Control: private, max-age=3600
   (No public CDN or browser cache sharing)
```

---

# 12. Commit History for This Milestone

| Commit Hash | Date | Description |
|---|---|---|
| `2026-04-30 09:00` | Phase 1 | ImageServiceInterface, ImageService, AppServiceProvider binding, filesystem config |
| `2026-04-30 10:50` | Phase 2 | Resources (10) with authenticated route URLs; FormRequests (16) with file validation |
| `2026-04-30 12:45` | Phase 3 | 10 controllers refactored with DIP injection, image lifecycle methods, 30+ API routes |
| `2026-04-30 14:30` | Phase 4 | Docker volume, Nginx deny rules, entrypoint bootstrap |

**Tag:** `v0.4.0-private-image-management`

---

# 13. Known Constraints and Future Considerations

| Topic | Current State | Future Option |
|---|---|---|
| **Storage backend** | Local Docker volume | S3 / Cloudflare R2 via new `ImageServiceInterface` implementation |
| **Image processing** | Raw upload, no resize | Add `intervention/image` for resize/compression on upload |
| **CDN** | None | Signed S3 URLs for time-limited CDN delivery |
| **Virus scanning** | None | ClamAV integration before `storage::putFileAs()` |
| **Backup** | Manual `docker volume` | Automated snapshot to Azure Blob Storage |
| **Gym logo** | Private (same as all images) | Could be made semi-public for WordPress frontend integration |

> The gym logo is currently private and accessible only to authenticated API users. When the WordPress integration is implemented, a decision must be made whether to expose the logo publicly (requires a dedicated public endpoint without auth) or to proxy it through the WordPress PHP backend.
