<?php

namespace App\Http\Controllers;

use App\Models\StaffAttendance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Handles staff clock-in/out and attendance records.
 *
 * SRP: Solely responsible for handling HTTP requests related to staff attendance.
 */
class StaffAttendanceController extends Controller
{
    /**
     * Returns attendance records. Admin/manager sees all for their gym; staff sees own.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve attendance records.'];

        try {
            $query = $request->user()->isAdmin()
                ? StaffAttendance::query()
                : StaffAttendance::forStaff($request->user()->id);

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single attendance record. Admin, manager, or own record.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve attendance record.'];

        try {
            $record = StaffAttendance::findOrFail($id);

            if (!$request->user()->isAdvanced() && $request->user()->id !== $record->staff_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $record;
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
        $result      = false;
        $messageArray = ['general' => 'Could not clock in.'];

        try {
            $result = StaffAttendance::create([
                'staff_id' => $request->user()->id,
                'gym_id'   => $request->user()->current_gym_id,
                'clock_in' => Carbon::now(),
                'date'     => Carbon::today()->toDateString(),
            ]);
            $messageArray = ['general' => 'Clocked in.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Sets the clock-out time for an existing attendance record. Own record only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function clockOut(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not clock out.'];

        try {
            $record = StaffAttendance::findOrFail($id);

            if ($request->user()->id !== $record->staff_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $record->update(['clock_out' => Carbon::now()]);
            $messageArray = ['general' => 'Clocked out.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an attendance record. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete attendance record.'];

        try {
            StaffAttendance::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Attendance record deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
