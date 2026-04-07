<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for gym rooms.
 *
 * SRP: Solely responsible for handling HTTP requests related to rooms.
 * DIP: Delegates authorization decisions to RoomPolicy via the Gate contract.
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
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve rooms.'];

        try {
            $query = Room::query();

            if ($request->filled('gym_id')) {
                $query->where('gym_id', (int) $request->input('gym_id'));
            }

            $result       = RoomResource::collection($query->paginate(10)->withQueryString());
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
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve room.'];

        try {
            $room = Room::findOrFail($id);
            $this->authorize('view', $room);

            $result       = new RoomResource($room);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new room. Advanced staff within their own gym (enforced by RoomPolicy).
     *
     * @param  StoreRoomRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create room.'];

        try {
            $this->authorize('create', Room::class);

            $result       = new RoomResource(Room::create($request->validated()));
            $messageArray = ['general' => 'Room created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing room. Advanced staff within their own gym (enforced by RoomPolicy).
     *
     * @param  UpdateRoomRequest  $request
     * @param  int                $id
     * @return JsonResponse
     */
    public function update(UpdateRoomRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update room.'];

        try {
            $room = Room::findOrFail($id);
            $this->authorize('update', $room);

            $result       = $room->update($request->validated());
            $messageArray = ['general' => 'Room updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a room. Admin only (enforced by RoomPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete room.'];

        try {
            $room = Room::findOrFail($id);
            $this->authorize('delete', $room);

            $room->delete();
            $result       = true;
            $messageArray = ['general' => 'Room deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
