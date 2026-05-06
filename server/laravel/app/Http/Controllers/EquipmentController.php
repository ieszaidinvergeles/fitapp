<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreEquipmentRequest;
use App\Http\Requests\UpdateEquipmentRequest;
use App\Http\Resources\EquipmentResource;
use App\Models\Equipment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD and image operations for equipment.
 *
 * SRP: Solely responsible for handling HTTP requests related to equipment.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to EquipmentPolicy via the Gate contract.
 */
class EquipmentController extends Controller
{
    /**
     * The image service used for file I/O operations.
     *
     * @var ImageServiceInterface
     */
    private ImageServiceInterface $imageService;

    /**
     * Subfolder name inside the private images disk used for this entity.
     *
     * @var string
     */
    private const IMAGE_FOLDER = 'equipment';

    /**
     * @param  ImageServiceInterface  $imageService  Injected by the service container.
     */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a paginated list of all equipment.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $paginated    = Equipment::paginate(6)->withQueryString();
            $result       = [
                'data' => EquipmentResource::collection($paginated),
                'meta' => [
                    'current_page' => $paginated->currentPage(),
                    'last_page'    => $paginated->lastPage(),
                    'per_page'     => $paginated->perPage(),
                    'total'        => $paginated->total(),
                ]
            ];
            return response()->json([
                'result' => $result,
                'message' => ['general' => 'OK']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'result' => false,
                'message' => ['general' => $e->getMessage()]
            ], 500);
        }
    }

    /**
     * Returns a single equipment item by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve equipment.'];

        try {
            $equipment    = Equipment::findOrFail($id);
            $this->authorize('view', $equipment);

            $result       = new EquipmentResource($equipment);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new equipment item. Admin only (enforced by EquipmentPolicy + Gate::before).
     * If an image file is provided it is stored in private storage.
     *
     * @param  StoreEquipmentRequest  $request
     * @return JsonResponse
     */
    public function store(StoreEquipmentRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create equipment.'];

        try {
            $this->authorize('create', Equipment::class);

            $equipment = Equipment::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $equipment->id);
                $equipment->update(['image_url' => $path]);
            }

            $result       = new EquipmentResource($equipment->fresh());
            $messageArray = ['general' => 'Equipment created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing equipment item. Admin only (enforced by EquipmentPolicy + Gate::before).
     * If a new image file is provided the old file is replaced.
     *
     * @param  UpdateEquipmentRequest  $request
     * @param  int                     $id
     * @return JsonResponse
     */
    public function update(UpdateEquipmentRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update equipment.'];

        try {
            $equipment = Equipment::findOrFail($id);
            $this->authorize('update', $equipment);

            $equipment->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $equipment->id, $equipment->image_url);
                $equipment->update(['image_url' => $path]);
            }

            $result       = new EquipmentResource($equipment->fresh());
            $messageArray = ['general' => 'Equipment updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an equipment item and removes its associated image from private storage.
     * Admin only (enforced by EquipmentPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete equipment.'];

        try {
            $equipment = Equipment::findOrFail($id);
            $this->authorize('delete', $equipment);

            $this->imageService->delete($equipment->image_url);
            $equipment->delete();

            $result       = true;
            $messageArray = ['general' => 'Equipment deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the equipment image from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $equipment = Equipment::findOrFail($id);

            if (!$equipment->image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($equipment->image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the equipment image in private storage.
     * Authorization: admin only (same as update).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not upload image.'];

        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);

            $equipment = Equipment::findOrFail($id);
            $this->authorize('update', $equipment);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $equipment->id, $equipment->image_url);
            $equipment->update(['image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the equipment image from private storage and clears the database field.
     * Authorization: admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function deleteImage(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete image.'];

        try {
            $equipment = Equipment::findOrFail($id);
            $this->authorize('delete', $equipment);

            $this->imageService->delete($equipment->image_url);
            $equipment->update(['image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
