<?php

namespace App\Http\Controllers;

use App\Http\Resources\StaffAttendanceResource;
use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Handles staff clock-in/out and attendance records.
 *
 * SRP: Solely responsible for handling HTTP requests related to staff attendance.
 * DIP: Delegates authorization decisions to StaffAttendancePolicy via the Gate contract.
 */
class StaffAttendanceController extends Controller
{
    /**
     * Returns attendance records.
     * Admins see all; managers see records from their gym; staff members see their own.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve attendance records.'];

        try {
            $this->authorize('viewAny', StaffAttendance::class);

            $user  = $request->user();
            $query = $user->isAdmin()
                ? StaffAttendance::query()
                : ($user->isManager()
                    ? StaffAttendance::where('gym_id', $user->current_gym_id)
                    : StaffAttendance::forStaff($user->id));

            $result       = StaffAttendanceResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single attendance record.
     * Access is determined by StaffAttendancePolicy (own record or own gym for managers).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve attendance record.'];

        try {
            $record = StaffAttendance::findOrFail($id);
            $this->authorize('view', $record);

            $result       = new StaffAttendanceResource($record);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new clock-in record for the authenticated staff member.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clockIn(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not clock in.'];

        try {
            $this->authorize('create', StaffAttendance::class);

            $result = new StaffAttendanceResource(StaffAttendance::create([
                'staff_id' => $request->user()->id,
                'gym_id'   => $request->user()->current_gym_id,
                'clock_in' => Carbon::now(),
                'date'     => Carbon::today()->toDateString(),
            ]));
            $messageArray = ['general' => 'Clocked in.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Sets the clock-out time for an existing attendance record.
     * A staff member may only clock out their own record (enforced by StaffAttendancePolicy).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function clockOut(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not clock out.'];

        try {
            $record = StaffAttendance::findOrFail($id);
            $this->authorize('update', $record);

            $result       = $record->update(['clock_out' => Carbon::now()]);
            $messageArray = ['general' => 'Clocked out.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an attendance record. Admin only (enforced by StaffAttendancePolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete attendance record.'];

        try {
            $record = StaffAttendance::findOrFail($id);
            $this->authorize('delete', $record);

            $record->delete();
            $result       = true;
            $messageArray = ['general' => 'Attendance record deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
