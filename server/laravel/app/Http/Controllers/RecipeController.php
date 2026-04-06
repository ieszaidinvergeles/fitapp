<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecipeRequest;
use App\Http\Requests\UpdateRecipeRequest;
use App\Models\Recipe;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for recipes.
 *
 * SRP: Solely responsible for handling HTTP requests related to recipes.
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve recipes.'];

        try {
            $query = Recipe::query();

            if ($request->filled('type')) {
                $type = limpiarCampo($request->input('type'));
                $query->byType($type);
            }

            if ($request->filled('max_calories')) {
                $max = limpiarNumeros($request->input('max_calories'));
                $query->byCalorieRange(0, (int) $max);
            }

            $result      = $query->paginate(10)->withQueryString();
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve recipe.'];

        try {
            $result      = Recipe::findOrFail($id);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new recipe. Advanced only.
     *
     * @param  StoreRecipeRequest  $request
     * @return JsonResponse
     */
    public function store(StoreRecipeRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create recipe.'];

        try {
            $result      = Recipe::create($request->validated());
            $messageArray = ['general' => 'Recipe created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing recipe. Advanced only.
     *
     * @param  UpdateRecipeRequest  $request
     * @param  int                  $id
     * @return JsonResponse
     */
    public function update(UpdateRecipeRequest $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update recipe.'];

        try {
            $recipe      = Recipe::findOrFail($id);
            $result      = $recipe->update($request->validated());
            $messageArray = ['general' => 'Recipe updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a recipe. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete recipe.'];

        try {
            Recipe::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Recipe deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
