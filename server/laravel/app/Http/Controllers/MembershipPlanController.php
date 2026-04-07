<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;

/**
 * Handles CRUD operations for membership plans.
 *
 * SRP: Solely responsible for handling HTTP requests related to membership plans.
 * OCP: New plan-related endpoints are added as new methods without modifying existing ones.
 * DIP: Delegates authorization decisions to MembershipPlanPolicy via the Gate contract.
 */
class MembershipPlanController extends Controller
{
    /**
     * Returns a paginated list of all membership plans.
     * Publicly available (no authorization required).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve membership plans.'];

        try {
            $result       = MembershipPlan::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single membership plan by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve membership plan.'];

        try {
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('view', $plan);

            $result       = $plan;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new membership plan. Admin only (enforced by MembershipPlanPolicy + Gate::before).
     *
     * @param  StoreMembershipPlanRequest  $request
     * @return JsonResponse
     */
    public function store(StoreMembershipPlanRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create membership plan.'];

        try {
            $this->authorize('create', MembershipPlan::class);

            $result       = MembershipPlan::create($request->validated());
            $messageArray = ['general' => 'Membership plan created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing membership plan. Admin only (enforced by MembershipPlanPolicy + Gate::before).
     *
     * @param  UpdateMembershipPlanRequest  $request
     * @param  int                          $id
     * @return JsonResponse
     */
    public function update(UpdateMembershipPlanRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update membership plan.'];

        try {
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $result       = $plan->update($request->validated());
            $messageArray = ['general' => 'Membership plan updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a membership plan. Admin only (enforced by MembershipPlanPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete membership plan.'];

        try {
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('delete', $plan);

            $plan->delete();
            $result       = true;
            $messageArray = ['general' => 'Membership plan deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
