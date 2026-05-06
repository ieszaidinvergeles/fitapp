<?php

namespace App\Http\Controllers;

use App\Contracts\ImageServiceInterface;
use App\Http\Requests\StoreGymRequest;
use App\Http\Requests\UpdateGymRequest;
use App\Http\Resources\GymResource;
use App\Models\Gym;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Handles CRUD and management operations for gyms.
 *
 * SRP: Solely responsible for handling HTTP requests related to gyms.
 * DIP: Delegates authorization decisions to GymPolicy via the Gate contract.
 */
class GymController extends Controller
{
    /** @var ImageServiceInterface */
    private ImageServiceInterface $imageService;

    /** @var string */
    private const IMAGE_FOLDER = 'gyms';

    /** @param  ImageServiceInterface  $imageService */
    public function __construct(ImageServiceInterface $imageService)
    {
        $this->imageService = $imageService;
    }
    /**
     * Returns a paginated list of all gyms.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve gyms.'];

        try {
            $result       = GymResource::collection(Gym::paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single gym by ID.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('view', $gym);

            $result       = new GymResource($gym);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new gym. Admin only (enforced by GymPolicy + Gate::before).
     *
     * @param  StoreGymRequest  $request
     * @return JsonResponse
     */
    public function store(StoreGymRequest $request): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create gym.'];

        try {
            $this->authorize('create', Gym::class);

            $gym = Gym::create($request->safe()->except('logo'));

            if ($request->hasFile('logo')) {
                $path = $this->imageService->upload($request->file('logo'), self::IMAGE_FOLDER, $gym->id);
                $gym->update(['logo_url' => $path]);
            }

            $result       = new GymResource($gym->fresh());
            $messageArray = ['general' => 'Gym created.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates an existing gym.
     * Managers may update their own gym; admins may update any (enforced by GymPolicy).
     *
     * @param  UpdateGymRequest  $request
     * @param  int               $id
     * @return JsonResponse
     */
    public function update(UpdateGymRequest $request, int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not update gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('update', $gym);

            $gym->update($request->safe()->except('logo'));

            if ($request->hasFile('logo')) {
                $path = $this->imageService->replace($request->file('logo'), self::IMAGE_FOLDER, $gym->id, $gym->logo_url);
                $gym->update(['logo_url' => $path]);
            }

            $result       = new GymResource($gym->fresh());
            $messageArray = ['general' => 'Gym updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a gym. Admin only (enforced by GymPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete gym.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('delete', $gym);

            $this->imageService->delete($gym->logo_url);
            $gym->delete();
            $result       = true;
            $messageArray = ['general' => 'Gym deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Assigns a manager to a gym. Admin only.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function assignManager(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not assign manager.'];

        try {
            $gym          = Gym::findOrFail($id);
            $result       = $gym->assignManager((int) $request->input('user_id'));
            $messageArray = ['general' => 'Manager assigned.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Streams the gym logo from private storage with private cache headers.
     * Authorization: any authenticated user.
     *
     * @param  int  $id
     * @return Response|JsonResponse
     */
    public function showLogo(int $id): Response|JsonResponse
    {
        try {
            $gym = Gym::findOrFail($id);

            if (!$gym->logo_url) {
                return response()->json(['result' => false, 'message' => ['general' => 'No logo found.']], 404);
            }

            return $this->imageService->stream($gym->logo_url);
        } catch (\Exception $e) {
            return response()->json(['result' => false, 'message' => ['general' => $e->getMessage()]], 404);
        }
    }

    /**
     * Uploads or replaces the gym logo in private storage.
     * Authorization: admin or gym manager.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function uploadLogo(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not upload logo.'];

        try {
            $request->validate(['logo' => 'required|image|mimes:jpeg,png,webp,gif|max:2048']);

            $gym = Gym::findOrFail($id);
            $this->authorize('update', $gym);

            $path = $this->imageService->replace($request->file('logo'), self::IMAGE_FOLDER, $gym->id, $gym->logo_url);
            $gym->update(['logo_url' => $path]);

            $result       = true;
            $messageArray = ['general' => 'Logo uploaded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes the gym logo from private storage and clears the database field.
     * Authorization: admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function deleteLogo(int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete logo.'];

        try {
            $gym = Gym::findOrFail($id);
            $this->authorize('delete', $gym);

            $this->imageService->delete($gym->logo_url);
            $gym->update(['logo_url' => null]);

            $result       = true;
            $messageArray = ['general' => 'Logo deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
