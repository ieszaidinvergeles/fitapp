<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Models\Routine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD and management operations for routines.
 *
 * SRP: Solely responsible for handling HTTP requests related to routines.
 */
class RoutineController extends Controller
{
    /**
     * Returns a paginated list of all routines.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve routines.'];

        try {
            $result      = Routine::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single routine by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve routine.'];

        try {
            $result      = Routine::with('orderedExercises')->findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new routine. Advanced only.
     *
     * @param  StoreRoutineRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoutineRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create routine.'];

        try {
            $result      = Routine::create($request->validated());
            $messageArray = ['general' => 'Routine created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing routine. Advanced or own creator.
     *
     * @param  UpdateRoutineRequest  $request
     * @param  int                   $id
     * @return JsonResponse
     */
    public function update(UpdateRoutineRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update routine.'];

        try {
            $routine = Routine::findOrFail($id);

            if (!$request->user()->isAdvanced() && $request->user()->id !== $routine->creator_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $routine->update($request->validated());
            $messageArray = ['general' => 'Routine updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a routine. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete routine.'];

        try {
            Routine::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Routine deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Adds an exercise to a routine with pivot data. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function addExercise(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not add exercise.'];

        try {
            $routine = Routine::findOrFail($id);
            $routine->exercises()->syncWithoutDetaching([
                $request->input('exercise_id') => [
                    'order_index'      => $request->input('order_index'),
                    'recommended_sets' => $request->input('recommended_sets'),
                    'recommended_reps' => $request->input('recommended_reps'),
                    'rest_seconds'     => $request->input('rest_seconds'),
                ],
            ]);
            $result      = true;
            $messageArray = ['general' => 'Exercise added.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Removes an exercise from a routine. Advanced only.
     *
     * @param  int  $routineId
     * @param  int  $exerciseId
     * @return JsonResponse
     */
    public function removeExercise(int $routineId, int $exerciseId): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not remove exercise.'];

        try {
            $routine = Routine::findOrFail($routineId);
            $routine->exercises()->detach($exerciseId);
            $result      = true;
            $messageArray = ['general' => 'Exercise removed.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Reorders exercises in a routine via an ordered array of exercise IDs. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function reorder(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not reorder exercises.'];

        try {
            $routine    = Routine::findOrFail($id);
            $exerciseIds = $request->input('exercise_ids', []);

            foreach ($exerciseIds as $index => $exerciseId) {
                $routine->exercises()->updateExistingPivot($exerciseId, ['order_index' => $index + 1]);
            }

            $result      = true;
            $messageArray = ['general' => 'Exercises reordered.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Activates a routine for the authenticated user. Authenticated.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not activate routine.'];

        try {
            $routine = Routine::findOrFail($id);
            $routine->activeUsers()->syncWithoutDetaching([
                $request->user()->id => [
                    'is_active'  => true,
                    'start_date' => now()->toDateString(),
                ],
            ]);
            $result      = true;
            $messageArray = ['general' => 'Routine activated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Duplicates a routine for the authenticated advanced user. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not duplicate routine.'];

        try {
            $routine = Routine::findOrFail($id);
            $result  = $routine->duplicate($request->user()->id);
            $messageArray = ['general' => 'Routine duplicated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
