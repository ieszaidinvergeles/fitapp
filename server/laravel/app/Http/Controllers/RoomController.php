<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Http\Resources\RoomResource;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD and image operations for gym rooms.
 *
 * SRP: Solely responsible for handling HTTP requests related to rooms.
 * DIP: Depends on ImageServiceInterface and delegates authorization to RoomPolicy.
 */
class RoomController extends Controller
{
    /** @var ImageServiceInterface */
    private ImageServiceInterface $imageService;

    /** @var string */
    private const IMAGE_FOLDER = 'rooms';

    /** @param  ImageServiceInterface  $imageService */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /** @return JsonResponse */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve rooms.'];
        try {
            $query = Room::query();
            if ($request->filled('gym_id')) {
                $query->where('gym_id', (int) $request->input('gym_id'));
            }
            $result       = RoomResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve room.'];
        try {
            $room         = Room::findOrFail($id);
            $this->authorize('view', $room);
            $result       = new RoomResource($room);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function store(StoreRoomRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create room.'];
        try {
            $this->authorize('create', Room::class);
            $room = Room::create($request->safe()->except('image'));
            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $room->id);
                $room->update(['image_url' => $path]);
            }
            $result       = new RoomResource($room->fresh());
            $messageArray = ['general' => 'Room created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function update(UpdateRoomRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update room.'];
        try {
            $room = Room::findOrFail($id);
            $this->authorize('update', $room);
            $room->update($request->safe()->except('image'));
            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $room->id, $room->image_url);
                $room->update(['image_url' => $path]);
            }
            $result       = new RoomResource($room->fresh());
            $messageArray = ['general' => 'Room updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete room.'];
        try {
            $room = Room::findOrFail($id);
            $this->authorize('delete', $room);
            $this->imageService->delete($room->image_url);
            $room->delete();
            $result       = true;
            $messageArray = ['general' => 'Room deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return Response|JsonResponse */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $room = Room::findOrFail($id);
            if (!$room->image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }
            return $this->imageService->stream($room->image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /** @return JsonResponse */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not upload image.'];
        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);
            $room = Room::findOrFail($id);
            $this->authorize('update', $room);
            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $room->id, $room->image_url);
            $room->update(['image_url' => $path]);
            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function deleteImage(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete image.'];
        try {
            $room = Room::findOrFail($id);
            $this->authorize('delete', $room);
            $this->imageService->delete($room->image_url);
            $room->update(['image_url' => null]);
            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
