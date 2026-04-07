<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGymRequest;
use App\Http\Requests\UpdateGymRequest;
use App\Models\Gym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD and management operations for gyms.
 *
 * SRP: Solely responsible for handling HTTP requests related to gyms.
 * DIP: Delegates authorization decisions to GymPolicy via the Gate contract.
 */
class GymController extends Controller
{
    /**
     * Returns a paginated list of all gyms.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve gyms.'];

        try {
            $result       = Gym::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single gym by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('view', $gym);

            $result       = $gym;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new gym. Admin only (enforced by GymPolicy + Gate::before).
     *
     * @param  StoreGymRequest  $request
     * @return JsonResponse
     */
    public function store(StoreGymRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create gym.'];

        try {
            $this->authorize('create', Gym::class);

            $result       = Gym::create($request->validated());
            $messageArray = ['general' => 'Gym created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing gym.
     * Managers may update their own gym; admins may update any (enforced by GymPolicy).
     *
     * @param  UpdateGymRequest  $request
     * @param  int               $id
     * @return JsonResponse
     */
    public function update(UpdateGymRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('update', $gym);

            $result       = $gym->update($request->validated());
            $messageArray = ['general' => 'Gym updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a gym. Admin only (enforced by GymPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('delete', $gym);

            $gym->delete();
            $result       = true;
            $messageArray = ['general' => 'Gym deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Assigns a manager to a gym. Admin only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function assignManager(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not assign manager.'];

        try {
            $gym          = Gym::findOrFail($id);
            $result       = $gym->assignManager((int) $request->input('user_id'));
            $messageArray = ['general' => 'Manager assigned.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
