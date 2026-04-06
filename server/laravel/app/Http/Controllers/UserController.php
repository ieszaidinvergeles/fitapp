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
 */
class UserController extends Controller
{
    /**
     * Returns a paginated list of users, optionally filtered by role. Admin only.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve users.'];

        try {
            $query = User::query();

            if ($request->filled('role')) {
                $role = limpiarCampo($request->input('role'));
                $query->where('role', $role);
            }

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single user by ID. Admin or own user.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve user.'];

        try {
            $user = User::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $user;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new user (admin-created, not registration). Admin only.
     *
     * @param  StoreUserRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create user.'];

        try {
            $result      = User::create($request->validated());
            $messageArray = ['general' => 'User created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing user. Admin or own user.
     *
     * @param  UpdateUserRequest  $request
     * @param  int                $id
     * @return JsonResponse
     */
    public function update(UpdateUserRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update user.'];

        try {
            $user = User::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $user->id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $user->update($request->validated());
            $messageArray = ['general' => 'User updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a user. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete user.'];

        try {
            User::findOrFail($id)->delete();
            $result      = true;
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
        $result      = false;
        $messageArray = ['general' => 'Could not block user.'];

        try {
            $user        = User::findOrFail($id);
            $result      = $user->update(['is_blocked_from_booking' => true]);
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
        $result      = false;
        $messageArray = ['general' => 'Could not unblock user.'];

        try {
            $user        = User::findOrFail($id);
            $result      = $user->update(['is_blocked_from_booking' => false]);
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
        $result      = false;
        $messageArray = ['general' => 'Could not reset strikes.'];

        try {
            $user        = User::findOrFail($id);
            $result      = $user->update(['cancellation_strikes' => 0]);
            $messageArray = ['general' => 'Strikes reset.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
