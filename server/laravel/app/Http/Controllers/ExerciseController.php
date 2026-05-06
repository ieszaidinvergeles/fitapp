<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreExerciseRequest;
use App\Http\Requests\UpdateExerciseRequest;
use App\Http\Resources\ExerciseResource;
use App\Models\Exercise;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles CRUD and image operations for exercises.
 *
 * SRP: Solely responsible for handling HTTP requests related to exercises.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to ExercisePolicy via the Gate contract.
 */
class ExerciseController extends Controller
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
    private const IMAGE_FOLDER = 'exercises';

    /**
     * @param  ImageServiceInterface  $imageService  Injected by the service container.
     */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a paginated list of exercises, optionally filtered by muscle group.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve exercises.'];

        try {
            $query = Exercise::query();

            if ($request->filled('muscle_group')) {
                $query->byMuscleGroup($request->input('muscle_group'));
            }

            $result       = ExerciseResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single exercise by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve exercise.'];

        try {
            $exercise     = Exercise::findOrFail($id);
            $this->authorize('view', $exercise);

            $result       = new ExerciseResource($exercise);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new exercise. Advanced staff only (enforced by ExercisePolicy).
     * If an image file is provided it is stored in private storage.
     *
     * @param  StoreExerciseRequest  $request
     * @return JsonResponse
     */
    public function store(StoreExerciseRequest $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not create exercise.'];

        try {
            $this->authorize('create', Exercise::class);

            $exercise = Exercise::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $exercise->id);
                $exercise->update(['image_url' => $path]);
            }

            $result       = new ExerciseResource($exercise->fresh());
            $messageArray = ['general' => 'Exercise created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing exercise. Advanced staff only (enforced by ExercisePolicy).
     * If a new image file is provided the old file is replaced.
     *
     * @param  UpdateExerciseRequest  $request
     * @param  int                    $id
     * @return JsonResponse
     */
    public function update(UpdateExerciseRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not update exercise.'];

        try {
            $exercise = Exercise::findOrFail($id);
            $this->authorize('update', $exercise);

            $exercise->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $exercise->id, $exercise->image_url);
                $exercise->update(['image_url' => $path]);
            }

            $result       = new ExerciseResource($exercise->fresh());
            $messageArray = ['general' => 'Exercise updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes an exercise and removes its associated image from private storage.
     * Admin only (enforced by ExercisePolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete exercise.'];

        try {
            $exercise = Exercise::findOrFail($id);
            $this->authorize('delete', $exercise);

            $this->imageService->delete($exercise->image_url);
            $exercise->delete();

            $result       = true;
            $messageArray = ['general' => 'Exercise deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the exercise image from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $exercise = Exercise::findOrFail($id);

            if (!$exercise->image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($exercise->image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the exercise image in private storage.
     * Authorization: advanced staff only (same as update).
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

            $exercise = Exercise::findOrFail($id);
            $this->authorize('update', $exercise);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $exercise->id, $exercise->image_url);
            $exercise->update(['image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the exercise image from private storage and clears the database field.
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
            $exercise = Exercise::findOrFail($id);
            $this->authorize('delete', $exercise);

            $this->imageService->delete($exercise->image_url);
            $exercise->update(['image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
