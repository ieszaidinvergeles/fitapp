<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use App\Models\logs\NotificationDeliveryLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Handles broadcast notification operations.
 *
 * SRP: Solely responsible for handling HTTP requests related to notifications.
 * DIP: Delegates authorization decisions to NotificationPolicy via the Gate contract.
 */
class NotificationController extends Controller
{
    /**
     * Returns a paginated list of notifications targeted at the authenticated user.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function clientIndex(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve notifications.'];

        try {
            $user = $request->user();
            \Illuminate\Support\Facades\Log::info("Client Notifications Fetch", [
                'user_id' => $user?->id,
                'gym_id' => $user?->current_gym_id,
            ]);
            
            $query = Notification::where(function($q) use ($user) {
                $q->where('target_audience', 'global');
                if ($user->current_gym_id) {
                    $q->orWhere(function($sq) use ($user) {
                        $sq->where('target_audience', 'specific_gym')
                           ->where('related_gym_id', $user->current_gym_id);
                    });
                }
                // Include notifications explicitly sent to this user via logs
                $q->orWhereExists(function ($sq) use ($user) {
                    $sq->select(\Illuminate\Support\Facades\DB::raw(1))
                       ->from('notification_delivery_logs')
                       ->whereColumn('notification_delivery_logs.notification_id', 'notifications.id')
                       ->where('notification_delivery_logs.recipient_id', $user->id);
                });
            })
            ->leftJoin('notification_delivery_logs', function($join) use ($user) {
                $join->on('notifications.id', '=', 'notification_delivery_logs.notification_id')
                     ->where('notification_delivery_logs.recipient_id', '=', $user->id);
            })
            ->select('notifications.*', 'notification_delivery_logs.read_at')
            ->orderBy('notifications.created_at', 'desc');

            $paginated    = $query->paginate(15)->withQueryString();
            $result       = NotificationResource::collection($paginated)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a paginated list of notifications.
     * Only advanced staff may access this endpoint (enforced by NotificationPolicy).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve notifications.'];

        try {
            $this->authorize('viewAny', Notification::class);

            $result       = NotificationResource::collection(Notification::paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single notification by ID.
     * Only advanced staff may view notification details (enforced by NotificationPolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve notification.'];

        try {
            $notification = Notification::findOrFail($id);
            $this->authorize('view', $notification);

            $result       = new NotificationResource($notification);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a notification and writes delivery log entries for all resolved recipients.
     * Only advanced staff may send notifications (enforced by NotificationPolicy).
     *
     * @param  StoreNotificationRequest  $request
     * @return JsonResponse
     */
    public function store(StoreNotificationRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create notification.'];

        try {
            $this->authorize('create', Notification::class);

            $data               = $request->validated();
            $data['sender_id']  = $request->user()->id;
            $data['created_at'] = Carbon::now();

            $notification = Notification::create($data);
            $recipients   = $notification->resolveRecipients();

            foreach ($recipients as $recipient) {
                NotificationDeliveryLog::create([
                    'notification_id' => $notification->id,
                    'recipient_id'    => $recipient->id,
                    'channel'         => 'in_app',
                    'status'          => 'delivered',
                    'delivered_at'    => Carbon::now(),
                    'created_at'      => Carbon::now(),
                ]);
            }

            $result       = new NotificationResource($notification);
            $messageArray = ['general' => 'Notification sent to ' . $recipients->count() . ' recipients.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns the count of unread notifications for the authenticated user.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function countUnread(Request $request): JsonResponse
    {
        $user = $request->user();
        $count = Notification::where(function($q) use ($user) {
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

        return response()->json(['result' => ['count' => $count], 'message' => ['general' => 'OK']]);
    }

    /**
     * Marks all notifications targeted at the authenticated user as read.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function markAllAsRead(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not mark notifications as read.'];

        try {
            $user = $request->user();
            
            // 1. Update all existing logs for this user to 'read'
            NotificationDeliveryLog::where('recipient_id', $user->id)
                ->where('status', '!=', 'read')
                ->update([
                    'status'  => 'read',
                    'read_at' => Carbon::now()
                ]);

            // 2. Find notifications the user SHOULD see but has no log entry for yet
            $visibleNotifs = Notification::where(function($q) use ($user) {
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
                    ->where('notification_delivery_logs.recipient_id', $user->id);
            })->get();

            foreach ($visibleNotifs as $n) {
                NotificationDeliveryLog::create([
                    'notification_id' => $n->id,
                    'recipient_id'    => $user->id,
                    'channel'         => 'in_app',
                    'status'          => 'read',
                    'delivered_at'    => Carbon::now(),
                    'read_at'         => Carbon::now()
                ]);
            }

            $result       = true;
            $messageArray = ['general' => 'All notifications marked as read.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a notification. Admin only (enforced by NotificationPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete notification.'];

        try {
            $notification = Notification::findOrFail($id);
            $this->authorize('delete', $notification);

            $notification->delete();
            $result       = true;
            $messageArray = ['general' => 'Notification deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
