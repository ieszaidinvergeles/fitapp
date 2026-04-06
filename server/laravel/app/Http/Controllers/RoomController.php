<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for gym rooms.
 *
 * SRP: Solely responsible for handling HTTP requests related to rooms.
 */
class RoomController extends Controller
{
    /**
     * Returns a paginated list of rooms, optionally scoped to a gym.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve rooms.'];

        try {
            $query = Room::query();

            if ($request->filled('gym_id')) {
                $gymId = limpiarNumeros($request->input('gym_id'));
                $query->where('gym_id', $gymId);
            }

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single room by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve room.'];

        try {
            $result      = Room::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new room. Advanced only.
     *
     * @param  StoreRoomRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create room.'];

        try {
            $result      = Room::create($request->validated());
            $messageArray = ['general' => 'Room created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing room. Advanced only.
     *
     * @param  UpdateRoomRequest  $request
     * @param  int                $id
     * @return JsonResponse
     */
    public function update(UpdateRoomRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update room.'];

        try {
            $room        = Room::findOrFail($id);
            $result      = $room->update($request->validated());
            $messageArray = ['general' => 'Room updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a room. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete room.'];

        try {
            Room::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Room deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
