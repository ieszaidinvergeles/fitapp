<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;

/**
 * Handles CRUD operations for activity types.
 *
 * SRP: Solely responsible for handling HTTP requests related to activities.
 */
class ActivityController extends Controller
{
    /**
     * Returns a paginated list of all activities.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve activities.'];

        try {
            $result      = Activity::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single activity by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve activity.'];

        try {
            $result      = Activity::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new activity. Admin only.
     *
     * @param  StoreActivityRequest  $request
     * @return JsonResponse
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create activity.'];

        try {
            $result      = Activity::create($request->validated());
            $messageArray = ['general' => 'Activity created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing activity. Admin only.
     *
     * @param  UpdateActivityRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateActivityRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update activity.'];

        try {
            $activity    = Activity::findOrFail($id);
            $result      = $activity->update($request->validated());
            $messageArray = ['general' => 'Activity updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an activity. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete activity.'];

        try {
            Activity::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Activity deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
