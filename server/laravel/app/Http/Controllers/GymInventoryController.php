<?php

namespace App\Http\Controllers;

use App\Http\Resources\GymInventoryResource;
use App\Models\GymInventory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class GymInventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result = false;
        $messageArray = ['general' => 'Could not retrieve gym inventory.'];

        try {
            $perPage = max(1, min(50, (int)$request->input('per_page', 10)));

            $query = GymInventory::with(['gym', 'equipment'])
                ->when($request->filled('gym_id'), function ($query) use ($request) {
                    $query->where('gym_id', (int)$request->input('gym_id'));
                })
                ->orderBy('gym_id')
                ->orderBy('equipment_id');

            $paginator = $query->paginate($perPage)->withQueryString();

            $result = GymInventoryResource::collection($paginator)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    public function show(int $gymId, int $equipmentId): JsonResponse
    {
        /** @var mixed $result */ $result = false;
        $messageArray = ['general' => 'Could not retrieve gym inventory item.'];

        try {
            $inventory = GymInventory::with(['gym', 'equipment'])
                ->where('gym_id', $gymId)
                ->where('equipment_id', $equipmentId)
                ->firstOrFail();

            $result = new GymInventoryResource($inventory);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    public function store(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result = false;
        $messageArray = ['general' => 'Could not create gym inventory item.'];

        try {
            $validated = $request->validate($this->rules());

            DB::table('gym_inventory')->updateOrInsert(
                [
                    'gym_id'       => (int)$validated['gym_id'],
                    'equipment_id' => (int)$validated['equipment_id'],
                ],
                [
                    'quantity' => (int)$validated['quantity'],
                    'status'   => $validated['status'],
                ]
            );

            $inventory = $this->findInventory((int)$validated['gym_id'], (int)$validated['equipment_id']);
            $result = new GymInventoryResource($inventory);
            $messageArray = ['general' => 'Gym inventory item saved.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    public function update(Request $request, int $gymId, int $equipmentId): JsonResponse
    {
        /** @var mixed $result */ $result = false;
        $messageArray = ['general' => 'Could not update gym inventory item.'];

        try {
            $this->findInventory($gymId, $equipmentId);
            $validated = $request->validate($this->rules());

            DB::table('gym_inventory')
                ->where('gym_id', $gymId)
                ->where('equipment_id', $equipmentId)
                ->delete();

            DB::table('gym_inventory')->updateOrInsert(
                [
                    'gym_id'       => (int)$validated['gym_id'],
                    'equipment_id' => (int)$validated['equipment_id'],
                ],
                [
                    'quantity' => (int)$validated['quantity'],
                    'status'   => $validated['status'],
                ]
            );

            $inventory = $this->findInventory((int)$validated['gym_id'], (int)$validated['equipment_id']);
            $result = new GymInventoryResource($inventory);
            $messageArray = ['general' => 'Gym inventory item updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    public function destroy(int $gymId, int $equipmentId): JsonResponse
    {
        /** @var mixed $result */ $result = false;
        $messageArray = ['general' => 'Could not delete gym inventory item.'];

        try {
            $deleted = DB::table('gym_inventory')
                ->where('gym_id', $gymId)
                ->where('equipment_id', $equipmentId)
                ->delete();

            $result = $deleted > 0;
            $messageArray = ['general' => $deleted > 0 ? 'Gym inventory item deleted.' : 'Inventory item not found.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    private function findInventory(int $gymId, int $equipmentId): GymInventory
    {
        return GymInventory::with(['gym', 'equipment'])
            ->where('gym_id', $gymId)
            ->where('equipment_id', $equipmentId)
            ->firstOrFail();
    }

    private function rules(): array
    {
        return [
            'gym_id'       => ['required', 'integer', 'exists:gyms,id'],
            'equipment_id' => ['required', 'integer', 'exists:equipment,id'],
            'quantity'     => ['required', 'integer', 'min:0'],
            'status'       => ['required', Rule::in(['operational', 'maintenance', 'retired'])],
        ];
    }
}
