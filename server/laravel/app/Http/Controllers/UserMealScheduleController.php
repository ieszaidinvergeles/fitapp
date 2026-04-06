<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserMealScheduleRequest;
use App\Http\Requests\UpdateUserMealScheduleRequest;
use App\Models\UserMealSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for user meal schedule entries.
 *
 * SRP: Solely responsible for handling HTTP requests related to meal scheduling.
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve meal schedule.'];

        try {
            $query = UserMealSchedule::where('user_id', $request->user()->id);

            if ($request->filled('date')) {
                $date = limpiarCampo($request->input('date'));
                $query->forDate($date);
            }

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single meal entry. Own or admin.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $entry->user_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $entry;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a meal schedule entry for own user.
     *
     * @param  StoreUserMealScheduleRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserMealScheduleRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create meal entry.'];

        try {
            $data            = $request->validated();
            $data['user_id'] = $request->user()->id;
            $result          = UserMealSchedule::create($data);
            $messageArray    = ['general' => 'Meal entry created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates a meal entry — primarily for marking as consumed. Own user only.
     *
     * @param  UpdateUserMealScheduleRequest  $request
     * @param  int                            $id
     * @return JsonResponse
     */
    public function update(UpdateUserMealScheduleRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);

            if ($request->user()->id !== $entry->user_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $entry->update($request->validated());
            $messageArray = ['general' => 'Meal entry updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a meal entry. Own user or admin.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete meal entry.'];

        try {
            $entry = UserMealSchedule::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $entry->user_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $entry->delete();
            $result      = true;
            $messageArray = ['general' => 'Meal entry deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
