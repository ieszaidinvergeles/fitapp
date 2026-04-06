<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGymClassRequest;
use App\Http\Requests\UpdateGymClassRequest;
use App\Models\GymClass;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD and management operations for gym class sessions.
 *
 * SRP: Solely responsible for handling HTTP requests related to gym classes.
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve classes.'];

        try {
            $query = GymClass::query();

            if ($request->filled('gym_id')) {
                $query->where('gym_id', limpiarNumeros($request->input('gym_id')));
            }

            if ($request->filled('date')) {
                $query->where('start_time', 'like', limpiarCampo($request->input('date')) . '%');
            }

            $result      = $query->paginate(10)->withQueryString();
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve class.'];

        try {
            $result      = GymClass::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new class session with room and instructor conflict validation. Advanced only.
     *
     * @param  StoreGymClassRequest  $request
     * @return JsonResponse
     */
    public function store(StoreGymClassRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create class.'];

        try {
            $data  = $request->validated();
            $start = \Illuminate\Support\Carbon::parse($data['start_time']);
            $end   = \Illuminate\Support\Carbon::parse($data['end_time']);

            if (!empty($data['room_id'])) {
                $room = \App\Models\Room::findOrFail($data['room_id']);
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

            $result      = GymClass::create($data);
            $messageArray = ['general' => 'Class created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing class. Advanced only.
     *
     * @param  UpdateGymClassRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateGymClassRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update class.'];

        try {
            $gymClass    = GymClass::findOrFail($id);
            $result      = $gymClass->update($request->validated());
            $messageArray = ['general' => 'Class updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a class. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete class.'];

        try {
            GymClass::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Class deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Cancels a class and all its active bookings. Admin or manager.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function cancel(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not cancel class.'];

        try {
            $gymClass    = GymClass::findOrFail($id);
            $result      = $gymClass->cancel();
            $messageArray = ['general' => 'Class cancelled.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Marks all active bookings for a class as attended. Staff or above.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function markAttendance(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not mark attendance.'];

        try {
            $gymClass = GymClass::findOrFail($id);
            $gymClass->markAllAttended();
            $result      = true;
            $messageArray = ['general' => 'Attendance marked.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
