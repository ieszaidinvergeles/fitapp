<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

/**
 * Handles CRUD and administrative operations for users.
 *
 * SRP: Solely responsible for handling HTTP requests related to user management.
 * DIP: Delegates authorization decisions to UserPolicy via the Gate contract.
 */
class UserController extends Controller
{
    /** @var ImageServiceInterface */
    private ImageServiceInterface $imageService;

    /** @var string */
    private const IMAGE_FOLDER = 'users';

    /** @param  ImageServiceInterface  $imageService */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Returns a paginated list of users, optionally filtered by role.
     * Access restricted to advanced staff and above by middleware and Policy.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve users.'];
        $statusCode   = 200;

        try {
            $this->authorize('viewAny', User::class);

            $query = User::query();

            if ($request->filled('role')) {
                $query->where('role', $request->input('role'));
            }

            $result       = UserResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Returns a single user by ID.
     * A non-advanced user may only view their own profile (enforced by UserPolicy).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve user.'];
        $statusCode   = 200;

        try {
            $user = User::findOrFail($id);
            $this->authorize('view', $user);

            $result       = new UserResource($user);
            $messageArray = ['general' => 'OK'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Creates a new user (admin-created, not self-registration).
     * Restricted to advanced staff by route middleware and UserPolicy.
     *
     * @param  StoreUserRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create user.'];
        $statusCode   = 200;

        try {
            $this->authorize('create', User::class);

            $data = $request->validated();
            $this->guardRoleAssignment($request->user(), $data);
            
            // Explicitly hash the password since the 'hashed' cast is removed
            $data['password_hash'] = \Hash::make($data['password_hash']);
            
            // Ensure administrative defaults if not provided
            $data['membership_status']       = $data['membership_status'] ?? 'expired';
            $data['cancellation_strikes']    = $data['cancellation_strikes'] ?? 0;
            $data['is_blocked_from_booking'] = $data['is_blocked_from_booking'] ?? false;

            $user = User::create($data);
            
            $result       = new UserResource($user);
            $messageArray = ['general' => 'User created.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Updates an existing user profile.
     * A non-advanced user may only update their own profile (enforced by UserPolicy).
     *
     * @param  UpdateUserRequest  $request
     * @param  int                $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update user.'];
        $statusCode   = 200;

        try {
            $user = User::findOrFail($id);
            $this->authorize('update', $user);

            $data = $request->validated();
            $this->guardRoleAssignment($request->user(), $data, $user);

            if (array_key_exists('password_hash', $data) && $data['password_hash']) {
                $data['password_hash'] = \Hash::make($data['password_hash']);
            }

            $result       = $user->update($data);
            $messageArray = ['general' => 'User updated.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Deletes a user. Admin only (handled by route middleware and Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete user.'];
        $statusCode   = 200;

        try {
            $user = User::findOrFail($id);
            $this->authorize('delete', $user);

            $this->imageService->delete($user->profile_photo_url);
            $user->delete();
            $result       = true;
            $messageArray = ['general' => 'User deleted.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Blocks a user from making bookings. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function block(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not block user.'];
        $statusCode   = 200;

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['is_blocked_from_booking' => true]);
            $messageArray = ['general' => 'User blocked from booking.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Unblocks a user from making bookings. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function unblock(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not unblock user.'];
        $statusCode   = 200;

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['is_blocked_from_booking' => false]);
            $messageArray = ['general' => 'User unblocked.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Resets a user's cancellation strike counter to zero. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function resetStrikes(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not reset strikes.'];
        $statusCode   = 200;

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['cancellation_strikes' => 0]);
            $messageArray = ['general' => 'Strikes reset.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Prevents non-admin roles from assigning or mutating privileged roles.
     *
     * Assistants may only create or update client-facing users.
     * Managers may not create or promote admins.
     *
     * @param  User  $actor
     * @param  array<string, mixed>  $data
     * @param  User|null  $target
     * @return void
     */
    private function guardRoleAssignment(User $actor, array $data, ?User $target = null): void
    {
        $targetRole = $target?->role;
        $requestedRole = $data['role'] ?? $targetRole;

        if ($actor->isAdmin()) {
            return;
        }

        if ($actor->isAssistant()) {
            $allowedRoles = ['client', 'user_online'];

            if (($targetRole !== null && !in_array($targetRole, $allowedRoles, true))
                || ($requestedRole !== null && !in_array($requestedRole, $allowedRoles, true))) {
                throw new HttpException(403, 'Assistant users may only manage client-facing accounts.');
            }

            return;
        }

        if ($actor->isManager()) {
            if ($targetRole === 'admin' || $requestedRole === 'admin') {
                throw new HttpException(403, 'Managers may not create or modify admin accounts.');
            }
        }
    }

    /**
     * Streams the user profile photo from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showPhoto(int $id): Response|JsonResponse
    {
        try {
            $user = User::findOrFail($id);

            if (!$user->profile_photo_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No photo found.']], 404);
            }

            return $this->imageService->stream($user->profile_photo_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces a user's profile photo.
     *
     * Anti-impersonation rules:
     *   - user_online: may only update their own photo.
     *   - staff: cannot change their own photo; must be done by a superior.
     *   - assistant, manager, admin: may update any subordinate's photo.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function uploadPhoto(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not upload photo.'];
        $statusCode   = 200;

        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);

            $actor  = $request->user();
            $target = User::findOrFail($id);

            $this->guardPhotoUpload($actor, $target);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $target->id, $target->profile_photo_url);
            $target->update(['profile_photo_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Photo uploaded.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Deletes a user's profile photo from private storage and clears the database field.
     * Authorization: admin or the user's superior (same rules as upload).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function deletePhoto(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete photo.'];
        $statusCode   = 200;

        try {
            $actor  = $request->user();
            $target = User::findOrFail($id);

            $this->guardPhotoUpload($actor, $target);

            $this->imageService->delete($target->profile_photo_url);
            $target->update(['profile_photo_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Photo deleted.'];
        } catch (HttpExceptionInterface $e) {
            $messageArray = ['general' => $e->getMessage()];
            $statusCode   = $e->getStatusCode();
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray], $statusCode);
    }

    /**
     * Allows the authenticated user to update their own basic profile info.
     */
    public function updateMe(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $data = $request->validate([
                'username'  => 'sometimes|required|string|max:20|unique:users,username,' . $user->id,
                'full_name' => 'sometimes|nullable|string|max:160',
            ]);

            $user->update($data);

            return response()->json([
                'result' => new UserResource($user),
                'message' => ['general' => 'Profile updated successfully.']
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['result' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 422);
        }
    }

    /**
     * Allows the authenticated user to upload their own profile photo.
     */
    public function uploadMyPhoto(Request $request): JsonResponse
    {
        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);

            $user = $request->user();
            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $user->id, $user->profile_photo_url);
            $user->update(['profile_photo_url' => $path]);

            return response()->json([
                'result' => true,
                'message' => ['general' => 'Photo uploaded successfully.']
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['result' => false, 'message' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 422);
        }
    }

    /**
     * Enforces anti-impersonation rules for profile photo changes.
     *
     * @param  User  $actor
     * @param  User  $target
     * @return void
     * @throws HttpException
     */
    private function guardPhotoUpload(User $actor, User $target): void
    {
        if ($actor->isAdmin()) {
            return;
        }

        if ($actor->role === 'user_online') {
            if ($actor->id !== $target->id) {
                throw new HttpException(403, 'Online users may only change their own photo.');
            }
            return;
        }

        if ($actor->role === 'staff') {
            throw new HttpException(403, 'Staff photo changes must be made by a superior to prevent identity impersonation.');
        }

        if (in_array($actor->role, ['manager', 'assistant'], true)) {
            if (in_array($target->role, ['admin', 'manager'], true) && $actor->id !== $target->id) {
                throw new HttpException(403, 'You may not change the photo of a user with equal or higher privilege.');
            }
        }
    }
}
