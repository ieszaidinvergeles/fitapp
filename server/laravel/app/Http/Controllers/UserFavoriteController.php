<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserFavoriteRequest;
use App\Models\UserFavorite;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles operations for user favourite entities.
 *
 * SRP: Solely responsible for handling HTTP requests related to user favourites.
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
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve favourites.'];

        try {
            $query = UserFavorite::where('user_id', $request->user()->id);

            if ($request->filled('entity_type')) {
                $type = limpiarCampo($request->input('entity_type'));
                $query->ofType($type);
            }

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new favourite entry for own user. Validates no duplicates.
     *
     * @param  StoreUserFavoriteRequest  $request
     * @return JsonResponse
     */
    public function store(StoreUserFavoriteRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not add favourite.'];

        try {
            $userId     = $request->user()->id;
            $entityType = $request->input('entity_type');
            $entityId   = (int) $request->input('entity_id');

            if (UserFavorite::existsFor($userId, $entityType, $entityId)) {
                return response()->json(['result' => false, 'message' => ['general' => 'Already in favourites.']], 422);
            }

            $result      = UserFavorite::create([
                'user_id'     => $userId,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
            ]);
            $messageArray = ['general' => 'Added to favourites.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a favourite. Own user or admin.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not remove favourite.'];

        try {
            $favorite = UserFavorite::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $favorite->user_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $favorite->delete();
            $result      = true;
            $messageArray = ['general' => 'Removed from favourites.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
