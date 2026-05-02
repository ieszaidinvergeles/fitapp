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
