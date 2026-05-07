<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGymClassRequest;
use App\Http\Requests\UpdateGymClassRequest;
use App\Http\Resources\GymClassResource;
use App\Models\GymClass;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Handles CRUD and management operations for gym class sessions.
 *
 * SRP: Solely responsible for handling HTTP requests related to gym classes.
 * DIP: Delegates authorization decisions to GymClassPolicy via the Gate contract.
 */
class GymClassController extends Controller
{
    /**
     * Returns a paginated list of classes with optional gym_id and date filters.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve classes.'];

        try {
            $query = GymClass::with(['activity', 'instructor', 'room'])
                ->withCount(['bookings' => function($q) { $q->where('status', 'active'); }]);

            if ($request->filled('gym_id')) {
                $query->where('gym_id', (int) $request->input('gym_id'));
            }

            if ($request->filled('date')) {
                $query->whereDate('start_time', $request->input('date'));
                // Note: We remove the 'start_time > now' filter here to allow viewing history if the user searches for it.
            } elseif (!$request->boolean('include_past')) {
                // By default only show future classes
                $query->where('start_time', '>', now());
            }

            if ($request->filled('activity_id')) {
                $query->where('activity_id', (int) $request->input('activity_id'));
            }

            $query->orderBy('start_time', 'asc');

            $paginated = $query->paginate(5)->withQueryString();
            $result = GymClassResource::collection($paginated)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single class by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve class.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $this->authorize('view', $gymClass);

            $result       = new GymClassResource($gymClass);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new class session with room and instructor conflict validation.
     * Restricted to advanced staff within their own gym (enforced by GymClassPolicy).
     *
     * @param  StoreGymClassRequest  $request
     * @return JsonResponse
     */
    public function store(StoreGymClassRequest $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not create class.'];

        try {
            $this->authorize('create', GymClass::class);

            $data  = $request->validated();
            $start = Carbon::parse($data['start_time']);
            $end   = Carbon::parse($data['end_time']);

            if (!empty($data['room_id'])) {
                $room = Room::findOrFail($data['room_id']);
                if ($room->hasConflict($start, $end)) {
                    return response()->json(['result' => false, 'message' => ['general' => 'Room is already booked for this time slot.']], 422);
                }
            }

            if (!empty($data['instructor_id'])) {
                $gymClass = new GymClass();
                if ($gymClass->hasInstructorConflict($data['instructor_id'], $start, $end)) {
                    return response()->json(['result' => false, 'message' => ['general' => 'Instructor has a scheduling conflict.']], 422);
                }
            }

            $result       = new GymClassResource(GymClass::create($data));
            $messageArray = ['general' => 'Class created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing class.
     * Restricted to advanced staff within their own gym (enforced by GymClassPolicy).
     *
     * @param  UpdateGymClassRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateGymClassRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not update class.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $this->authorize('update', $gymClass);

            $result       = $gymClass->update($request->validated());
            $messageArray = ['general' => 'Class updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a class.
     * Restricted to advanced staff within their own gym (enforced by GymClassPolicy via Gate::before for admins).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete class.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $this->authorize('delete', $gymClass);

            $gymClass->delete();
            $result       = true;
            $messageArray = ['general' => 'Class deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Cancels a class and all its active bookings.
     * Restricted to advanced staff within their own gym (enforced by GymClassPolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not cancel class.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $this->authorize('delete', $gymClass);

            $result       = $gymClass->cancel();
            $messageArray = ['general' => 'Class cancelled.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Marks all active bookings for a class as attended.
     * Restricted to advanced staff within their own gym (enforced by GymClassPolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function markAttendance(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not mark attendance.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $this->authorize('update', $gymClass);

            $gymClass->markAllAttended();
            $result       = true;
            $messageArray = ['general' => 'Attendance marked.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
