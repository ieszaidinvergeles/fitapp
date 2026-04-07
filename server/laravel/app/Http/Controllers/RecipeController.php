<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Http\Resources\RecipeResource;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for recipes.
 *
 * SRP: Solely responsible for handling HTTP requests related to recipes.
 * DIP: Delegates authorization decisions to RecipePolicy via the Gate contract.
 */
class RecipeController extends Controller
{
    /**
     * Returns a paginated list of recipes with optional type and calorie filters.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve recipes.'];

        try {
            $query = Recipe::query();

            if ($request->filled('type')) {
                $query->byType($request->input('type'));
            }

            if ($request->filled('max_calories')) {
                $query->byCalorieRange(0, (int) $request->input('max_calories'));
            }

            $result       = RecipeResource::collection($query->paginate(10)->withQueryString());
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
        $result       = false;
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
        $result       = false;
        $messageArray = ['general' => 'Could not create recipe.'];

        try {
            $this->authorize('create', Recipe::class);

            $result       = new RecipeResource(Recipe::create($request->validated()));
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
        $result       = false;
        $messageArray = ['general' => 'Could not update recipe.'];

        try {
            $recipe = Recipe::findOrFail($id);
            $this->authorize('update', $recipe);

            $result       = $recipe->update($request->validated());
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
        $result       = false;
        $messageArray = ['general' => 'Could not delete recipe.'];

        try {
            $recipe = Recipe::findOrFail($id);
            $this->authorize('delete', $recipe);

            $recipe->delete();
            $result       = true;
            $messageArray = ['general' => 'Recipe deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
