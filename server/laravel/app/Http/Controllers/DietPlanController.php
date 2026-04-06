<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDietPlanRequest;
use App\Http\Requests\UpdateDietPlanRequest;
use App\Models\DietPlan;
use Illuminate\Http\JsonResponse;

/**
 * Handles CRUD operations for diet plans.
 *
 * SRP: Solely responsible for handling HTTP requests related to diet plans.
 */
class DietPlanController extends Controller
{
    /**
     * Returns a paginated list of all diet plans. Advanced only.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve diet plans.'];

        try {
            $result      = DietPlan::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single diet plan by ID. Advanced only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve diet plan.'];

        try {
            $result      = DietPlan::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new diet plan. Admin only.
     *
     * @param  StoreDietPlanRequest  $request
     * @return JsonResponse
     */
    public function store(StoreDietPlanRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create diet plan.'];

        try {
            $result      = DietPlan::create($request->validated());
            $messageArray = ['general' => 'Diet plan created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing diet plan. Admin only.
     *
     * @param  UpdateDietPlanRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateDietPlanRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update diet plan.'];

        try {
            $plan        = DietPlan::findOrFail($id);
            $result      = $plan->update($request->validated());
            $messageArray = ['general' => 'Diet plan updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a diet plan. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete diet plan.'];

        try {
            DietPlan::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Diet plan deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
