<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserMealScheduleRequest;
use App\Http\Requests\UpdateUserMealScheduleRequest;
use App\Http\Resources\UserMealScheduleResource;
use App\Models\UserMealSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for user meal schedule entries.
 *
 * SRP: Solely responsible for handling HTTP requests related to meal scheduling.
 * DIP: Delegates authorization decisions to UserMealSchedulePolicy via the Gate contract.
 */
class UserMealScheduleController extends Controller
{
    /**
     * Returns a paginated list of meal entries for the authenticated user, with optional date filter.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve meal schedule.'];

        try {
            $this->authorize('viewAny', UserMealSchedule::class);

            $query = UserMealSchedule::where('user_id', $request->user()->id);

            if ($request->filled('date')) {
                $query->forDate($request->input('date'));
            }

            // Hide consumed ONLY if explicitly requested via switch
            if ($request->has('hide_consumed') && $request->boolean('hide_consumed')) {
                $query->where('is_consumed', false);
            }

            // Filter by date (past vs future/today) if requested
            if (!$request->boolean('include_past')) {
                $query->where('date', '>=', now()->toDateString());
            }

            $query->with('recipe')
                  ->orderBy('date', 'desc')
                  ->orderBy('is_consumed', 'asc')
                  ->orderBy('meal_type', 'asc');

            $paginated    = $query->paginate(5)->withQueryString();
            $result       = UserMealScheduleResource::collection($paginated)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single meal entry.
     * Access restricted to the owner (enforced by UserMealSchedulePolicy).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);
            $this->authorize('view', $entry);

            $result       = new UserMealScheduleResource($entry);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a meal schedule entry for the authenticated user.
     *
     * @param  StoreUserMealScheduleRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserMealScheduleRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create meal entry.'];

        try {
            $this->authorize('create', UserMealSchedule::class);

            $data            = $request->validated();
            $data['user_id'] = $request->user()->id;
            $data['is_consumed'] = $data['is_consumed'] ?? false;
            $result          = new UserMealScheduleResource(UserMealSchedule::create($data));
            $messageArray    = ['general' => 'Meal entry created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates a meal entry, primarily for marking as consumed.
     * Access restricted to the owner (enforced by UserMealSchedulePolicy).
     *
     * @param  UpdateUserMealScheduleRequest  $request
     * @param  int                            $id
     * @return JsonResponse
     */
    public function update(UpdateUserMealScheduleRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);
            $this->authorize('update', $entry);

            $result       = $entry->update($request->validated());
            $messageArray = ['general' => 'Meal entry updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a meal entry.
     * Access restricted to the owner (enforced by UserMealSchedulePolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);
            $this->authorize('delete', $entry);

            $entry->delete();
            $result       = true;
            $messageArray = ['general' => 'Meal entry deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
