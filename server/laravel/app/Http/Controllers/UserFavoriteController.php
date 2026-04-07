<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserFavoriteRequest;
use App\Http\Resources\UserFavoriteResource;
use App\Models\UserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles operations for user favourite entities.
 *
 * SRP: Solely responsible for handling HTTP requests related to user favourites.
 * DIP: Delegates authorization decisions to UserFavoritePolicy via the Gate contract.
 */
class UserFavoriteController extends Controller
{
    /**
     * Returns all favourites for the authenticated user, with optional entity_type filter.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve favourites.'];

        try {
            $this->authorize('viewAny', UserFavorite::class);

            $query = UserFavorite::where('user_id', $request->user()->id);

            if ($request->filled('entity_type')) {
                $query->ofType($request->input('entity_type'));
            }

            $result       = UserFavoriteResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new favourite entry for the authenticated user. Validates no duplicates.
     *
     * @param  StoreUserFavoriteRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserFavoriteRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not add favourite.'];

        try {
            $this->authorize('create', UserFavorite::class);

            $userId     = $request->user()->id;
            $entityType = $request->input('entity_type');
            $entityId   = (int) $request->input('entity_id');

            if (UserFavorite::existsFor($userId, $entityType, $entityId)) {
                return response()->json(['result' => false, 'message' => ['general' => 'Already in favourites.']], 422);
            }

            $result       = new UserFavoriteResource(UserFavorite::create([
                'user_id'     => $userId,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
            ]));
            $messageArray = ['general' => 'Added to favourites.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a favourite entry.
     * A user may only remove their own favourites (enforced by UserFavoritePolicy).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not remove favourite.'];

        try {
            $favorite = UserFavorite::findOrFail($id);
            $this->authorize('delete', $favorite);

            $favorite->delete();
            $result       = true;
            $messageArray = ['general' => 'Removed from favourites.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
