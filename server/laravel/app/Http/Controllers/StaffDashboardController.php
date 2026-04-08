<?php

namespace App\Http\Controllers;

use App\Http\Resources\GymClassResource;
use App\Http\Resources\GymResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\StaffAttendanceResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\Gym;
use App\Models\GymClass;
use App\Models\Notification;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provides aggregate dashboard data for authenticated staff, manager, and admin users.
 *
 * SRP: Solely responsible for aggregating and returning the staff-dashboard payload.
 * DIP: Scopes queries to the authenticated user's gym and delegates formatting
 *      to existing Resource classes rather than building raw response arrays.
 */
class StaffDashboardController extends Controller
{
    /**
     * Returns all data required to render the staff management dashboard in one call.
     *
     * Admin users receive metrics across all gyms.
     * Manager and staff users receive metrics scoped to their assigned gym only.
     *
     * Payload includes:
     *   - Gym(s) overview
     *   - Today's scheduled gym classes with booking counts
     *   - Total active member count (scoped to gym)
     *   - Staff clocked in today
     *   - Last 10 pending notifications
     *   - Upcoming classes with low occupancy (< 30% in the next 48 hours)
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not load staff dashboard.'];

        try {
            $this->authorize('viewAny', Gym::class);

            $user     = $request->user();
            $isAdmin  = $user->isAdmin();
            $gymId    = $user->current_gym_id;

            $gymQuery = $isAdmin
                ? Gym::with('manager')
                : Gym::with('manager')->where('id', $gymId);

            $gyms = $gymQuery->get();

            $gymIds = $isAdmin
                ? $gyms->pluck('id')->toArray()
                : [$gymId];

            $todayStart = now()->startOfDay();
            $todayEnd   = now()->endOfDay();

            $todayClasses = GymClass::with(['activity', 'room', 'instructor'])
                ->whereIn('gym_id', $gymIds)
                ->whereBetween('start_time', [$todayStart, $todayEnd])
                ->where('is_cancelled', false)
                ->get();

            $todayBookingsCount = Booking::whereIn('class_id', $todayClasses->pluck('id'))
                ->where('status', 'active')
                ->count();

            $activeMembersCount = User::whereIn('current_gym_id', $gymIds)
                ->where('membership_status', 'active')
                ->whereIn('role', ['client', 'user_online'])
                ->count();

            $staffPresentToday = StaffAttendance::with('staff')
                ->whereIn('gym_id', $gymIds)
                ->whereDate('date', today())
                ->get();

            $pendingNotifications = Notification::whereIn('related_gym_id', $gymIds)
                ->orWhere('target_audience', 'global')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get();

            $cutoff = now()->addHours(48);

            $lowBookingClasses = GymClass::with(['activity', 'room'])
                ->whereIn('gym_id', $gymIds)
                ->where('start_time', '>', now())
                ->where('start_time', '<=', $cutoff)
                ->where('is_cancelled', false)
                ->whereNotNull('capacity_limit')
                ->get()
                ->filter(function (GymClass $gymClass): bool {
                    return $gymClass->occupancyPercentage() < 30;
                })
                ->values();

            $result = [
                'gyms'                  => GymResource::collection($gyms),
                'today_classes'         => GymClassResource::collection($todayClasses),
                'today_bookings_count'  => $todayBookingsCount,
                'active_members_count'  => $activeMembersCount,
                'staff_present_today'   => StaffAttendanceResource::collection($staffPresentToday),
                'pending_notifications' => NotificationResource::collection($pendingNotifications),
                'low_booking_classes'   => GymClassResource::collection($lowBookingClasses),
            ];

            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
