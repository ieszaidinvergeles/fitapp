<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreActivityRequest;
use App\Http\Requests\UpdateActivityRequest;
use App\Http\Resources\ActivityResource;
use App\Models\Activity;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD and image operations for activity types.
 *
 * SRP: Solely responsible for handling HTTP requests related to activities.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to ActivityPolicy via the Gate contract.
 */
class ActivityController extends Controller
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
    private const IMAGE_FOLDER = 'activities';

    /**
     * @param  ImageServiceInterface  $imageService  Injected by the service container.
     */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a paginated list of all activities.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve activities.'];

        try {
            $result       = ActivityResource::collection(Activity::paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single activity by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve activity.'];

        try {
            $activity     = Activity::findOrFail($id);
            $this->authorize('view', $activity);

            $result       = new ActivityResource($activity);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new activity. Admin only (enforced by ActivityPolicy + Gate::before).
     * If an image file is provided it is stored in private storage.
     *
     * @param  StoreActivityRequest  $request
     * @return JsonResponse
     */
    public function store(StoreActivityRequest $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not create activity.'];

        try {
            $this->authorize('create', Activity::class);

            $activity = Activity::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $activity->id);
                $activity->update(['image_url' => $path]);
            }

            $result       = new ActivityResource($activity->fresh());
            $messageArray = ['general' => 'Activity created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing activity. Admin only (enforced by ActivityPolicy + Gate::before).
     * If a new image file is provided the old file is replaced.
     *
     * @param  UpdateActivityRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateActivityRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not update activity.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('update', $activity);

            $activity->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $activity->id, $activity->image_url);
                $activity->update(['image_url' => $path]);
            }

            $result       = new ActivityResource($activity->fresh());
            $messageArray = ['general' => 'Activity updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an activity and removes its associated image from private storage.
     * Admin only (enforced by ActivityPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete activity.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('delete', $activity);

            $this->imageService->delete($activity->image_url);
            $activity->delete();

            $result       = true;
            $messageArray = ['general' => 'Activity deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the activity image from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $activity = Activity::findOrFail($id);

            if (!$activity->image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($activity->image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the activity image in private storage.
     * Authorization: admin only (same as update).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not upload image.'];

        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);

            $activity = Activity::findOrFail($id);
            $this->authorize('update', $activity);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $activity->id, $activity->image_url);
            $activity->update(['image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the activity image from private storage and clears the database field.
     * Authorization: admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function deleteImage(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete image.'];

        try {
            $activity = Activity::findOrFail($id);
            $this->authorize('delete', $activity);

            $this->imageService->delete($activity->image_url);
            $activity->update(['image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
