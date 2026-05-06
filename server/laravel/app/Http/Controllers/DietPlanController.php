<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreDietPlanRequest;
use App\Http\Requests\UpdateDietPlanRequest;
use App\Http\Resources\DietPlanResource;
use App\Models\DietPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Exception;

/**
 * Handles CRUD and image operations for diet plans.
 *
 * SRP: Solely responsible for handling HTTP requests related to diet plans.
 */
class DietPlanController extends Controller
{
    /** @var ImageServiceInterface */
    private ImageServiceInterface $imageService;

    /** @var string */
    private const IMAGE_FOLDER = 'diet_plans';

    /** @param  ImageServiceInterface  $imageService */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /** @return JsonResponse */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plans.'];
        try {
            $this->authorize('viewAny', DietPlan::class);

            $query = DietPlan::query();

            if ($request->has('favorites')) {
                $user = $request->user() ?: Auth::guard('sanctum')->user();
                if (!$user) {
                    return response()->json(['result' => ['data' => []], 'message' => ['general' => 'Unauthorized']]);
                }
                $favoriteIds = DB::table('user_favorites')
                    ->where('user_id', $user->id)
                    ->where('entity_type', 'diet_plan')
                    ->pluck('entity_id');
                $query->whereIn('id', $favoriteIds);
            }

            $paginated = $query->paginate(5)->withQueryString();

            // Pre-calculate favorites
            $userFavs = [];
            $activeUser = $request->user() ?: Auth::guard('sanctum')->user();
            if ($activeUser) {
                $userFavs = DB::table('user_favorites')
                    ->where('user_id', $activeUser->id)
                    ->where('entity_type', 'diet_plan')
                    ->pluck('entity_id')
                    ->toArray();
            }

            foreach ($paginated as $plan) {
                $plan->is_favorite_flag = in_array($plan->id, $userFavs);
            }

            $result = DietPlanResource::collection($paginated)
                ->response()
                ->getData(true);

            $messageArray = ['general' => 'OK'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plan.'];
        try {
            $plan         = DietPlan::with('recipes')->findOrFail($id);
            $this->authorize('view', $plan);
            $result       = new DietPlanResource($plan);
            $messageArray = ['general' => 'OK'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function store(StoreDietPlanRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not create diet plan.'];
        try {
            $this->authorize('create', DietPlan::class);
            $plan = DietPlan::create($request->safe()->except('image'));
            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $plan->id);
                $plan->update(['cover_image_url' => $path]);
            }
            $result       = new DietPlanResource($plan->fresh());
            $messageArray = ['general' => 'Diet plan created.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function update(UpdateDietPlanRequest $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update diet plan.'];
        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('update', $plan);
            $plan->update($request->safe()->except('image'));
            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $plan->id, $plan->cover_image_url);
                $plan->update(['cover_image_url' => $path]);
            }
            $result       = new DietPlanResource($plan->fresh());
            $messageArray = ['general' => 'Diet plan updated.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete diet plan.'];
        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('delete', $plan);
            $this->imageService->delete($plan->cover_image_url);
            $plan->delete();
            $result       = true;
            $messageArray = ['general' => 'Diet plan deleted.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function addRecipe(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not add recipe to plan.'];

        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $request->validate([
                'recipe_id' => 'required|exists:recipes,id',
                'meal_type' => 'required|in:breakfast,lunch,dinner,snack,pre_workout,post_workout',
            ]);

            $plan->recipes()->syncWithoutDetaching([
                $request->input('recipe_id') => [
                    'meal_type' => $request->input('meal_type'),
                ],
            ]);

            $result       = true;
            $messageArray = ['general' => 'Recipe added to plan.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function removeRecipe(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not remove recipe from plan.'];

        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $request->validate([
                'recipe_id' => 'required|exists:recipes,id',
                'meal_type' => 'required|in:breakfast,lunch,dinner,snack,pre_workout,post_workout',
            ]);

            $plan->recipes()->wherePivot('meal_type', $request->input('meal_type'))->detach($request->input('recipe_id'));

            $result       = true;
            $messageArray = ['general' => 'Recipe removed from plan.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return Response|JsonResponse */
    public function showImage(int $id)
    {
        try {
            $plan = DietPlan::findOrFail($id);
            if (!$plan->cover_image_url) {
                return \response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }
            return $this->imageService->stream($plan->cover_image_url);
        } catch (Exception $e) {
            return \response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /** @return JsonResponse */
    public function uploadImage(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not upload image.'];
        try {
            $request->validate(['image' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);
            $plan = DietPlan::findOrFail($id);
            $this->authorize('update', $plan);
            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $plan->id, $plan->cover_image_url);
            $plan->update(['cover_image_url' => $path]);
            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function deleteImage(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not delete image.'];
        try {
            $plan = DietPlan::findOrFail($id);
            $this->authorize('delete', $plan);
            $this->imageService->delete($plan->cover_image_url);
            $plan->update(['cover_image_url' => null]);
            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Toggles a diet plan as a favorite for the authenticated user.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function favorite(Request $request, int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update favorites.'];

        try {
            $user = Auth::user();
            if (!$user) {
                return \response()->json(['result' => false, 'message' => ['general' => 'Unauthorized']], 401);
            }
            $userId = $user->id;
            $plan = DietPlan::findOrFail($id);

            $exists = DB::table('user_favorites')
                ->where('user_id', $userId)
                ->where('entity_type', 'diet_plan')
                ->where('entity_id', $id)
                ->exists();

            if ($exists) {
                DB::table('user_favorites')
                    ->where('user_id', $userId)
                    ->where('entity_type', 'diet_plan')
                    ->where('entity_id', $id)
                    ->delete();
                $messageArray = ['general' => 'Removed from favorites.'];
            } else {
                DB::table('user_favorites')->insert([
                    'user_id'     => $userId,
                    'entity_type' => 'diet_plan',
                    'entity_id'   => $id,
                ]);
                $messageArray = ['general' => 'Added to favorites.'];
            }
            $result = true;
        } catch (Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return \response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
