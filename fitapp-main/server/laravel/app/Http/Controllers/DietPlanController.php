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

/**
 * Handles CRUD and image operations for diet plans.
 *
 * SRP: Solely responsible for handling HTTP requests related to diet plans.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to DietPlanPolicy via the Gate contract.
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
    public function index(): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plans.'];
        try {
            $this->authorize('viewAny', DietPlan::class);
            $result       = DietPlanResource::collection(DietPlan::paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return JsonResponse */
    public function show(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve diet plan.'];
        try {
            $plan         = DietPlan::findOrFail($id);
            $this->authorize('view', $plan);
            $result       = new DietPlanResource($plan);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /** @return Response|JsonResponse */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $plan = DietPlan::findOrFail($id);
            if (!$plan->cover_image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }
            return $this->imageService->stream($plan->cover_image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
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
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
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
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }
        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
