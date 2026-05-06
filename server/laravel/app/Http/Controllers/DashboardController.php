<?php

namespace App\Http\Controllers;

use App\Http\Resources\BodyMetricResource;
use App\Http\Resources\BookingResource;
use App\Http\Resources\GymClassResource;
use App\Http\Resources\MembershipPlanResource;
use App\Http\Resources\NotificationResource;
use App\Http\Resources\RoutineResource;
use App\Http\Resources\UserResource;
use App\Models\Booking;
use App\Models\GymClass;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Provides aggregate dashboard data for authenticated client users.
 *
 * SRP: Solely responsible for aggregating and returning the user-dashboard payload.
 * DIP: Delegates formatting to existing Resource classes; depends on Eloquent
 *      relationship abstractions rather than raw query coupling.
 */
class DashboardController extends Controller
{
    /**
     * Returns all data required to render the user dashboard in a single call.
     *
     * Loads the following in one optimized pass:
     *   - Authenticated user with gym and membership plan
     *   - Active routine (first active pivot entry)
     *   - Next 5 upcoming active bookings with their gym class details
     *   - Most recent body metric record
     *   - Count of unread notifications targeted at the user
     *   - Next scheduled gym class at the user's gym
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not load dashboard.'];

        try {
            $user = $request->user()->load([
                'currentGym',
                'membershipPlan',
                'latestBodyMetric',
            ]);

            $activeRoutine = $user->currentActiveRoutine()->first();

            $upcomingBookings = Booking::with(['gymClass.activity', 'gymClass.room'])
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->whereHas('gymClass', function ($q): void {
                    $q->where('start_time', '>', now())->where('is_cancelled', false);
                })
                ->orderBy(
                    GymClass::select('start_time')
                        ->whereColumn('id', 'bookings.class_id')
                        ->limit(1)
                )
                ->limit(5)
                ->get();

            $unreadCount = Notification::where(function($q) use ($user) {
                $q->where('target_audience', 'global');
                if ($user->current_gym_id) {
                    $q->orWhere(function($sq) use ($user) {
                        $sq->where('target_audience', 'specific_gym')
                           ->where('related_gym_id', $user->current_gym_id);
                    });
                }
            })
            ->whereNotExists(function ($query) use ($user) {
                $query->select(\Illuminate\Support\Facades\DB::raw(1))
                    ->from('notification_delivery_logs')
                    ->whereColumn('notification_delivery_logs.notification_id', 'notifications.id')
                    ->where('notification_delivery_logs.recipient_id', $user->id)
                    ->where('notification_delivery_logs.status', 'read');
            })
            ->where('created_at', '>=', $user->created_at)
            ->count();

            $nextClass = null;
            if ($user->current_gym_id) {
                $nextClass = GymClass::with(['activity', 'room', 'instructor'])
                    ->withCount(['bookings' => function($q) { $q->where('status', 'active'); }])
                    ->where('gym_id', $user->current_gym_id)
                    ->where('start_time', '>', now())
                    ->where('is_cancelled', false)
                    ->orderBy('start_time')
                    ->first();
            }

            $result = [
                'user'                        => new UserResource($user),
                'membership'                  => $user->membershipPlan
                    ? new MembershipPlanResource($user->membershipPlan)
                    : null,
                'gym'                         => $user->currentGym
                    ? new \App\Http\Resources\GymResource($user->currentGym)
                    : null,
                'active_routine'              => $activeRoutine
                    ? new RoutineResource($activeRoutine)
                    : null,
                'upcoming_bookings'           => BookingResource::collection($upcomingBookings),
                'latest_metric'               => $user->latestBodyMetric
                    ? new BodyMetricResource($user->latestBodyMetric)
                    : null,
                'unread_notifications_count'  => $unreadCount,
                'next_class'                  => $nextClass
                    ? new GymClassResource($nextClass)
                    : null,
            ];

            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
