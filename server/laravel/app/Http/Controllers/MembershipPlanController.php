<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreMembershipPlanRequest;
use App\Http\Requests\UpdateMembershipPlanRequest;
use App\Http\Resources\MembershipPlanResource;
use App\Models\MembershipPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Handles CRUD and image operations for membership plans.
 *
 * SRP: Solely responsible for handling HTTP requests related to membership plans.
 * OCP: New plan-related endpoints are added as new methods without modifying existing ones.
 * DIP: Depends on ImageServiceInterface (not the concrete class) and delegates
 *      authorization to MembershipPlanPolicy via the Gate contract.
 */
class MembershipPlanController extends Controller
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
    private const IMAGE_FOLDER = 'membership_plans';

    /**
     * @param  ImageServiceInterface  $imageService  Injected by the service container.
     */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Returns a paginated list of all membership plans.
     * Publicly available (no authorization required).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve membership plans.'];

        try {
            $result       = MembershipPlanResource::collection(MembershipPlan::paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single membership plan by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not retrieve membership plan.'];

        try {
            $plan         = MembershipPlan::findOrFail($id);
            $this->authorize('view', $plan);

            $result       = new MembershipPlanResource($plan);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new membership plan. Admin only (enforced by MembershipPlanPolicy + Gate::before).
     * If an image file is provided it is stored in private storage.
     *
     * @param  StoreMembershipPlanRequest  $request
     * @return JsonResponse
     */
    public function store(StoreMembershipPlanRequest $request): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not create membership plan.'];

        try {
            $this->authorize('create', MembershipPlan::class);

            $plan = MembershipPlan::create($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->upload($request->file('image'), self::IMAGE_FOLDER, $plan->id);
                $plan->update(['badge_image_url' => $path]);
            }

            $result       = new MembershipPlanResource($plan->fresh());
            $messageArray = ['general' => 'Membership plan created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing membership plan. Admin only (enforced by MembershipPlanPolicy + Gate::before).
     * If a new image file is provided the old file is replaced.
     *
     * @param  UpdateMembershipPlanRequest  $request
     * @param  int                          $id
     * @return JsonResponse
     */
    public function update(UpdateMembershipPlanRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not update membership plan.'];

        try {
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $plan->update($request->safe()->except('image'));

            if ($request->hasFile('image')) {
                $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $plan->id, $plan->badge_image_url);
                $plan->update(['badge_image_url' => $path]);
            }

            $result       = new MembershipPlanResource($plan->fresh());
            $messageArray = ['general' => 'Membership plan updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a membership plan and removes its associated image from private storage.
     * Admin only (enforced by MembershipPlanPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */ $result       = false;
        $messageArray = ['general' => 'Could not delete membership plan.'];

        try {
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('delete', $plan);

            $this->imageService->delete($plan->badge_image_url);
            $plan->delete();

            $result       = true;
            $messageArray = ['general' => 'Membership plan deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the badge image from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showImage(int $id): Response|JsonResponse
    {
        try {
            $plan = MembershipPlan::findOrFail($id);

            if (!$plan->badge_image_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No image found.']], 404);
            }

            return $this->imageService->stream($plan->badge_image_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the membership plan badge image in private storage.
     * Authorization: admin only.
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

            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('update', $plan);

            $path = $this->imageService->replace($request->file('image'), self::IMAGE_FOLDER, $plan->id, $plan->badge_image_url);
            $plan->update(['badge_image_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Image uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the membership plan badge image from private storage and clears the database field.
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
            $plan = MembershipPlan::findOrFail($id);
            $this->authorize('delete', $plan);

            $this->imageService->delete($plan->badge_image_url);
            $plan->update(['badge_image_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Image deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
