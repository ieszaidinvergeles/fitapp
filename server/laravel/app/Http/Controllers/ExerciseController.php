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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve exercises.'];

        try {
            $query = Exercise::query();

            if ($request->filled('muscle_group')) {
                $group = limpiarCampo($request->input('muscle_group'));
                $query->byMuscleGroup($group);
            }

            $result      = $query->paginate(10)->withQueryString();
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve exercise.'];

        try {
            $result      = Exercise::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new exercise. Advanced only.
     *
     * @param  StoreExerciseRequest  $request
     * @return JsonResponse
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create exercise.'];

        try {
            $result      = Exercise::create($request->validated());
            $messageArray = ['general' => 'Exercise created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing exercise. Advanced only.
     *
     * @param  UpdateExerciseRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateExerciseRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update exercise.'];

        try {
            $exercise    = Exercise::findOrFail($id);
            $result      = $exercise->update($request->validated());
            $messageArray = ['general' => 'Exercise updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an exercise. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete exercise.'];

        try {
            Exercise::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Exercise deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
