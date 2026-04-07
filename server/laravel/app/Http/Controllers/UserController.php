<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD and administrative operations for users.
 *
 * SRP: Solely responsible for handling HTTP requests related to user management.
 * DIP: Delegates authorization decisions to UserPolicy via the Gate contract.
 */
class UserController extends Controller
{
    /**
     * Returns a paginated list of users, optionally filtered by role.
     * Access restricted to advanced staff and above by middleware and Policy.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve users.'];

        try {
            $this->authorize('viewAny', User::class);

            $query = User::query();

            if ($request->filled('role')) {
                $query->where('role', $request->input('role'));
            }

            $result       = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve user.'];

        try {
            $user = User::findOrFail($id);
            $this->authorize('view', $user);

            $result       = $user;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        $result       = false;
        $messageArray = ['general' => 'Could not create user.'];

        try {
            $this->authorize('create', User::class);

            $result       = User::create($request->validated());
            $messageArray = ['general' => 'User created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        $result       = false;
        $messageArray = ['general' => 'Could not update user.'];

        try {
            $user = User::findOrFail($id);
            $this->authorize('update', $user);

            $result       = $user->update($request->validated());
            $messageArray = ['general' => 'User updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a user. Admin only (handled by route middleware and Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete user.'];

        try {
            $user = User::findOrFail($id);
            $this->authorize('delete', $user);

            $user->delete();
            $result       = true;
            $messageArray = ['general' => 'User deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Blocks a user from making bookings. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function block(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not block user.'];

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['is_blocked_from_booking' => true]);
            $messageArray = ['general' => 'User blocked from booking.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Unblocks a user from making bookings. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function unblock(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not unblock user.'];

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['is_blocked_from_booking' => false]);
            $messageArray = ['general' => 'User unblocked.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Resets a user's cancellation strike counter to zero. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function resetStrikes(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not reset strikes.'];

        try {
            $user         = User::findOrFail($id);
            $result       = $user->update(['cancellation_strikes' => 0]);
            $messageArray = ['general' => 'Strikes reset.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
