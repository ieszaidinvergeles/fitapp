<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreRoutineRequest;
use App\Http\Requests\UpdateRoutineRequest;
use App\Http\Resources\RoutineResource;
use App\Models\Routine;
use App\Models\UserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD, management, and image operations for routines.
 *
 * SRP: Solely responsible for handling HTTP requests related to routines.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to RoutinePolicy via the Gate contract.
 */
class RoutineController extends Controller
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
    private const IMAGE_FOLDER = 'routines';

    /**
     * @param  ImageServiceInterface  $imageService  Injected by the service container.
     */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a paginated list of all routines.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve routines.'];

        try {
            $this->authorize('viewAny', Routine::class);
            
            $query = Routine::withCount('exercises');

            if ($request->has('favorites')) {
                $user = $request->user() ?: auth('sanctum')->user();
                if (!$user) {
                    return response()->json(['result' => ['data' => []], 'message' => ['general' => 'Unauthorized']]);
                }
                $favoriteIds = \Illuminate\Support\Facades\DB::table('user_favorites')
                    ->where('user_id', $user->id)
                    ->where('entity_type', 'routine')
                    ->pluck('entity_id');
                $query->whereIn('id', $favoriteIds);
            }

            $paginated = $query->paginate(6)->withQueryString();

            // Pre-calculate favorites to avoid N+1 queries in the Resource
            $userFavs = [];
            $activeUser = $request->user() ?: auth('sanctum')->user();
            if ($activeUser) {
                $userFavs = \Illuminate\Support\Facades\DB::table('user_favorites')
                    ->where('user_id', $activeUser->id)
                    ->where('entity_type', 'routine')
                    ->pluck('entity_id')
                    ->toArray();
            }

            foreach ($paginated as $routine) {
                $routine->is_favorite_flag = in_array($routine->id, $userFavs);
            }

            $result = RoutineResource::collection($paginated)
                ->response()
                ->getData(true);
            
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single routine by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve routine.'];

        try {
            $routine     = Routine::with('orderedExercises')->findOrFail($id);
            $result      = (new RoutineResource($routine))->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new routine. Advanced only.
     * If an image file is provided it is stored in private storage.
     *
     * @param  StoreRoutineRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRoutineRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create routine.'];

        try {
            $routine = Routine::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $routine->id);
                $routine->update(['cover_image_url' => $path]);
            }

            $result      = new RoutineResource($routine->fresh());
            $messageArray = ['general' => 'Routine created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing routine. Advanced or own creator.
     * If a new image file is provided the old file is replaced.
     *
     * @param  UpdateRoutineRequest  $request
     * @param  int                   $id
     * @return JsonResponse
     */
    public function update(UpdateRoutineRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update routine.'];

        try {
            $routine = Routine::findOrFail($id);
            $this->authorize('update', $routine);

            $routine->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $routine->id, $routine->cover_image_url);
                $routine->update(['cover_image_url' => $path]);
            }

            $result       = new RoutineResource($routine->fresh());
            $messageArray = ['general' => 'Routine updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a routine and removes its associated image from private storage.
     * Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete routine.'];

        try {
            $routine = Routine::findOrFail($id);

            $this->imageService->delete($routine->cover_image_url);
            $routine->delete();

            $result      = true;
            $messageArray = ['general' => 'Routine deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Adds an exercise to a routine with pivot data. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function addExercise(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not add exercise.'];

        try {
            $routine = Routine::findOrFail($id);
            $routine->exercises()->syncWithoutDetaching([
                $request->input('exercise_id') => [
                    'order_index'      => $request->input('order_index'),
                    'recommended_sets' => $request->input('recommended_sets'),
                    'recommended_reps' => $request->input('recommended_reps'),
                    'rest_seconds'     => $request->input('rest_seconds'),
                ],
            ]);
            $result      = true;
            $messageArray = ['general' => 'Exercise added.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Removes an exercise from a routine. Advanced only.
     *
     * @param  int  $routineId
     * @param  int  $exerciseId
     * @return JsonResponse
     */
    public function removeExercise(int $routineId, int $exerciseId): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not remove exercise.'];

        try {
            $routine = Routine::findOrFail($routineId);
            $routine->exercises()->detach($exerciseId);
            $result      = true;
            $messageArray = ['general' => 'Exercise removed.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Reorders exercises in a routine via an ordered array of exercise IDs. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function reorder(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not reorder exercises.'];

        try {
            $routine    = Routine::findOrFail($id);
            $exerciseIds = $request->input('exercise_ids', []);

            foreach ($exerciseIds as $index => $exerciseId) {
                $routine->exercises()->updateExistingPivot($exerciseId, ['order_index' => $index + 1]);
            }

            $result      = true;
            $messageArray = ['general' => 'Exercises reordered.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Activates a routine for the authenticated user. Authenticated.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function activate(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not activate routine.'];

        try {
            $routine = Routine::findOrFail($id);
            $routine->activeUsers()->syncWithoutDetaching([
                $request->user()->id => [
                    'is_active'  => true,
                    'start_date' => now()->toDateString(),
                ],
            ]);
            $result      = true;
            $messageArray = ['general' => 'Routine activated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Duplicates a routine for the authenticated advanced user. Advanced only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function duplicate(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not duplicate routine.'];

        try {
            $routine = Routine::findOrFail($id);
            $result  = $routine->duplicate($request->user()->id);
            $messageArray = ['general' => 'Routine duplicated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the routine cover image from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $routine = Routine::findOrFail($id);

            if (!$routine->cover_image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($routine->cover_image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the routine cover image in private storage.
     * Authorization: advanced staff (same as update).
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

            $routine = Routine::findOrFail($id);
            $this->authorize('update', $routine);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $routine->id, $routine->cover_image_url);
            $routine->update(['cover_image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the routine cover image from private storage and clears the database field.
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
            $routine = Routine::findOrFail($id);

            $this->imageService->delete($routine->cover_image_url);
            $routine->update(['cover_image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Toggles a routine as a favorite for the authenticated user.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function favorite(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update favorites.'];

        try {
            $userId = $request->user()->id;
            $routine = Routine::findOrFail($id);
            
            $exists = \Illuminate\Support\Facades\DB::table('user_favorites')
                ->where('user_id', $userId)
                ->where('entity_type', 'routine')
                ->where('entity_id', $id)
                ->exists();

            if ($exists) {
                \Illuminate\Support\Facades\DB::table('user_favorites')
                    ->where('user_id', $userId)
                    ->where('entity_type', 'routine')
                    ->where('entity_id', $id)
                    ->delete();
                $messageArray = ['general' => 'Removed from favorites.'];
            } else {
                \Illuminate\Support\Facades\DB::table('user_favorites')->insert([
                    'user_id'     => $userId,
                    'entity_type' => 'routine',
                    'entity_id'   => $id,
                ]);
                $messageArray = ['general' => 'Added to favorites.'];
            }
            $result = true;
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
