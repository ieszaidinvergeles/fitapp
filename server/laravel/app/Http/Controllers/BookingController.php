<?php

namespace App\Http\Controllers;

use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\GymClass;
use App\Models\logs\BookingHistory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/**
 * Handles booking operations for gym class sessions.
 *
 * SRP: Solely responsible for handling HTTP requests related to bookings.
 * DIP: Delegates authorization decisions to BookingPolicy via the Gate contract.
 */
class BookingController extends Controller
{
    /**
     * Returns a paginated list of bookings.
     * Advanced staff and admins see all; standard users see their own.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve bookings.'];

        try {
            $this->authorize('viewAny', Booking::class);

            $query = $request->user()->canManageOperations()
                ? Booking::with(['gymClass.activity', 'gymClass.room', 'gymClass.gym', 'user'])
                : Booking::with(['gymClass.activity', 'gymClass.room', 'gymClass.gym'])
                    ->where('user_id', $request->user()->id);

            if ($request->boolean('include_past')) {
                $query->whereHas('gymClass', function($q) {
                    $q->where('start_time', '<', now());
                });
            } else {
                $query->whereHas('gymClass', function($q) {
                    $q->where('start_time', '>', now());
                });
            }

            if ($request->boolean('hide_cancelled')) {
                $query->where('status', '!=', 'cancelled')
                      ->whereHas('gymClass', function($q) {
                          $q->where('is_cancelled', 0);
                      });
            }

            $query->orderBy(
                GymClass::select('start_time')
                    ->whereColumn('id', 'bookings.class_id')
                    ->limit(1),
                'desc'
            );

            $paginated = $query->paginate(5)->withQueryString();
            $result    = BookingResource::collection($paginated)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single booking by ID.
     * Access restricted to the owner or advanced staff (enforced by BookingPolicy).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve booking.'];

        try {
            $booking = Booking::findOrFail($id);
            $this->authorize('view', $booking);

            $result       = new BookingResource($booking);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new booking for the authenticated user.
     * Validates class availability, user eligibility, and duplicate booking.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not create booking.'];

        try {
            $this->authorize('create', Booking::class);

            $request->validate(['class_id' => ['required', 'integer', 'exists:classes,id']]);

            $classId  = (int) $request->input('class_id');
            $user     = $request->user();
            $gymClass = GymClass::findOrFail($classId);

            if ($gymClass->is_cancelled) {
                return response()->json(['result' => false, 'message' => ['general' => 'Class is cancelled.']], 422);
            }

            if ($gymClass->isFull()) {
                return response()->json(['result' => false, 'message' => ['general' => 'Class is full.']], 422);
            }

            if ($user->isBlockedFromBooking()) {
                return response()->json(['result' => false, 'message' => ['general' => 'Booking blocked: You have reached the limit of 3 cancellation strikes. Please contact the front desk to restore your booking privileges.']], 422);
            }

            if ($gymClass->isUserBooked($user->id)) {
                return response()->json(['result' => false, 'message' => ['general' => 'Already booked for this class.']], 422);
            }

            $booking = Booking::create([
                'class_id'  => $classId,
                'user_id'   => $user->id,
                'status'    => 'active',
                'booked_at' => Carbon::now(),
            ]);

            BookingHistory::create([
                'booking_id'    => $booking->id,
                'class_id'      => $booking->class_id,
                'user_id'       => $booking->user_id,
                'from_status'   => null,
                'to_status'     => 'active',
                'changed_by_id' => $user->id,
                'reason'        => 'Booking created',
            ]);

            $result       = new BookingResource($booking);
            $messageArray = ['general' => 'Booking created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Cancels a booking.
     * Access restricted to the owner or advanced staff (enforced by BookingPolicy).
     * Returns 422 if the class starts within 2 hours.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function cancel(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not cancel booking.'];

        try {
            $booking = Booking::findOrFail($id);
            $this->authorize('update', $booking);

            if (!$booking->isCancellable()) {
                return response()->json(['result' => false, 'message' => ['general' => 'Booking cannot be cancelled within 2 hours of class start.']], 422);
            }

            $booking->cancel();
            $result       = true;
            $messageArray = ['general' => 'Booking cancelled.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Hard deletes a booking.
     * Restricted to advanced staff (enforced by BookingPolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete booking.'];

        try {
            $booking = Booking::findOrFail($id);
            $this->authorize('delete', $booking);

            $booking->delete();
            $result       = true;
            $messageArray = ['general' => 'Booking deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
