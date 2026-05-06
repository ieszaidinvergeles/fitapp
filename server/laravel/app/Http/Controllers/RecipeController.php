<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD operations for recipes.
 *
 * SRP: Solely responsible for handling HTTP requests related to recipes.
 * DIP: Delegates authorization decisions to RecipePolicy via the Gate contract.
 */
class RecipeController extends Controller
{
    /** @var ImageServiceInterface */
    private ImageServiceInterface $imageService;

    /** @var string */
    private const IMAGE_FOLDER = 'recipes';

    /** @param  ImageServiceInterface  $imageService */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Returns a paginated list of recipes with optional type and calorie filters.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve recipes.'];

        try {
            $query = Recipe::query();

            if ($request->filled('type')) {
                $query->byType($request->input('type'));
            }

            if ($request->filled('max_calories')) {
                $query->byCalorieRange(0, (int) $request->input('max_calories'));
            }

            $perPage = (int) $request->input('per_page', 10);
            $paginator    = $query->paginate($perPage)->withQueryString();

            $result       = RecipeResource::collection($paginator)->response()->getData(true);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single recipe by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve recipe.'];

        try {
            $recipe = Recipe::findOrFail($id);
            $this->authorize('view', $recipe);

            $result       = new RecipeResource($recipe);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new recipe. Advanced staff only (enforced by RecipePolicy).
     *
     * @param  StoreRecipeRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRecipeRequest $request): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create recipe.'];

        try {
            $this->authorize('create', Recipe::class);

            $recipe = Recipe::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $recipe->id);
                $recipe->update(['image_url' => $path]);
            }

            $result       = new RecipeResource($recipe->fresh());
            $messageArray = ['general' => 'Recipe created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing recipe. Advanced staff only (enforced by RecipePolicy).
     *
     * @param  UpdateRecipeRequest  $request
     * @param  int                  $id
     * @return JsonResponse
     */
    public function update(UpdateRecipeRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update recipe.'];

        try {
            $recipe = Recipe::findOrFail($id);
            $this->authorize('update', $recipe);

            $recipe->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $recipe->id, $recipe->image_url);
                $recipe->update(['image_url' => $path]);
            }

            $result       = new RecipeResource($recipe->fresh());
            $messageArray = ['general' => 'Recipe updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a recipe. Admin only (enforced by RecipePolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete recipe.'];

        try {
            $recipe = Recipe::findOrFail($id);
            $this->authorize('delete', $recipe);

            $this->imageService->delete($recipe->image_url);
            $recipe->delete();
            $result       = true;
            $messageArray = ['general' => 'Recipe deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the recipe image from private storage with private cache headers.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $recipe = Recipe::findOrFail($id);

            if (!$recipe->image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($recipe->image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the recipe image in private storage.
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

            $recipe = Recipe::findOrFail($id);
            $this->authorize('update', $recipe);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $recipe->id, $recipe->image_url);
            $recipe->update(['image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the recipe image from private storage and clears the database field.
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
            $recipe = Recipe::findOrFail($id);
            $this->authorize('delete', $recipe);

            $this->imageService->delete($recipe->image_url);
            $recipe->update(['image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
