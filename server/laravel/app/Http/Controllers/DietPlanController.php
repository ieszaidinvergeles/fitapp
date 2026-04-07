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
 * DIP: Delegates authorization decisions to DietPlanPolicy via the Gate contract.
 */
class DietPlanController extends Controller
{
    /**
     * Returns a paginated list of all diet plans.
     * Restricted to advanced staff (enforced by DietPlanPolicy).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plans.'];

        try {
            $this->authorize('viewAny', DietPlan::class);

            $result       = DietPlan::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single diet plan by ID.
     * Restricted to advanced staff (enforced by DietPlanPolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plan.'];

        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('view', $plan);

            $result       = $plan;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new diet plan. Admin only (enforced by DietPlanPolicy + Gate::before).
     *
     * @param  StoreDietPlanRequest  $request
     * @return JsonResponse
     */
    public function store(StoreDietPlanRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create diet plan.'];

        try {
            $this->authorize('create', DietPlan::class);

            $result       = DietPlan::create($request->validated());
            $messageArray = ['general' => 'Diet plan created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing diet plan. Admin only (enforced by DietPlanPolicy + Gate::before).
     *
     * @param  UpdateDietPlanRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateDietPlanRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update diet plan.'];

        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $result       = $plan->update($request->validated());
            $messageArray = ['general' => 'Diet plan updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a diet plan. Admin only (enforced by DietPlanPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete diet plan.'];

        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('delete', $plan);

            $plan->delete();
            $result       = true;
            $messageArray = ['general' => 'Diet plan deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
