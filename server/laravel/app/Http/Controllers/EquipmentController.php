<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;

/**
 * Handles CRUD operations for equipment.
 *
 * SRP: Solely responsible for handling HTTP requests related to equipment.
 */
class EquipmentController extends Controller
{
    /**
     * Returns a paginated list of all equipment.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve equipment.'];

        try {
            $result      = Equipment::paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single equipment item by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve equipment.'];

        try {
            $result      = Equipment::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new equipment item. Admin only.
     *
     * @param  StoreEquipmentRequest  $request
     * @return JsonResponse
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create equipment.'];

        try {
            $result      = Equipment::create($request->validated());
            $messageArray = ['general' => 'Equipment created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing equipment item. Admin only.
     *
     * @param  UpdateEquipmentRequest  $request
     * @param  int                     $id
     * @return JsonResponse
     */
    public function update(UpdateEquipmentRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update equipment.'];

        try {
            $equipment   = Equipment::findOrFail($id);
            $result      = $equipment->update($request->validated());
            $messageArray = ['general' => 'Equipment updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an equipment item. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete equipment.'];

        try {
            Equipment::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Equipment deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
