<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for exercises.
 *
 * SRP: Solely responsible for handling HTTP requests related to exercises.
 * DIP: Delegates authorization decisions to ExercisePolicy via the Gate contract.
 */
class ExerciseController extends Controller
{
    /**
     * Returns a paginated list of exercises, optionally filtered by muscle group.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve exercises.'];

        try {
            $query = Exercise::query();

            if ($request->filled('muscle_group')) {
                $query->byMuscleGroup($request->input('muscle_group'));
            }

            $result       = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single exercise by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve exercise.'];

        try {
            $exercise = Exercise::findOrFail($id);
            $this->authorize('view', $exercise);

            $result       = $exercise;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new exercise. Advanced staff only (enforced by ExercisePolicy).
     *
     * @param  StoreExerciseRequest  $request
     * @return JsonResponse
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create exercise.'];

        try {
            $this->authorize('create', Exercise::class);

            $result       = Exercise::create($request->validated());
            $messageArray = ['general' => 'Exercise created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing exercise. Advanced staff only (enforced by ExercisePolicy).
     *
     * @param  UpdateExerciseRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateExerciseRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update exercise.'];

        try {
            $exercise = Exercise::findOrFail($id);
            $this->authorize('update', $exercise);

            $result       = $exercise->update($request->validated());
            $messageArray = ['general' => 'Exercise updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an exercise. Admin only (enforced by ExercisePolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete exercise.'];

        try {
            $exercise = Exercise::findOrFail($id);
            $this->authorize('delete', $exercise);

            $exercise->delete();
            $result       = true;
            $messageArray = ['general' => 'Exercise deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
