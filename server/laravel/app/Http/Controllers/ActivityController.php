<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;

/**
 * Handles CRUD operations for activity types.
 *
 * SRP: Solely responsible for handling HTTP requests related to activities.
 * DIP: Delegates authorization decisions to ActivityPolicy via the Gate contract.
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
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve activities.'];

        try {
            $result       = ActivityResource::collection(Activity::paginate(10)->withQueryString());
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
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve activity.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('view', $activity);

            $result       = new ActivityResource($activity);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new activity. Admin only (enforced by ActivityPolicy + Gate::before).
     *
     * @param  StoreActivityRequest  $request
     * @return JsonResponse
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create activity.'];

        try {
            $this->authorize('create', Activity::class);

            $result       = new ActivityResource(Activity::create($request->validated()));
            $messageArray = ['general' => 'Activity created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing activity. Admin only (enforced by ActivityPolicy + Gate::before).
     *
     * @param  UpdateActivityRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateActivityRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update activity.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('update', $activity);

            $result       = $activity->update($request->validated());
            $messageArray = ['general' => 'Activity updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an activity. Admin only (enforced by ActivityPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete activity.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('delete', $activity);

            $activity->delete();
            $result       = true;
            $messageArray = ['general' => 'Activity deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
