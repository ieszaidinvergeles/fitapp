<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBodyMetricRequest;
use App\Models\BodyMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for body metric records.
 *
 * SRP: Solely responsible for handling HTTP requests related to body metrics.
 */
class BodyMetricController extends Controller
{
    /**
     * Returns a paginated list of body metrics for the authenticated user or all (admin).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve body metrics.'];

        try {
            $query = $request->user()->isAdmin()
                ? BodyMetric::query()
                : BodyMetric::forUser($request->user()->id);

            $result      = $query->paginate(10)->withQueryString();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single body metric record. Own or admin.
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve body metric.'];

        try {
            $metric = BodyMetric::findOrFail($id);

            if (!$request->user()->isAdmin() && $request->user()->id !== $metric->user_id) {
                return response()->json(['result' => false, 'message' => ['general' => 'Forbidden.']], 403);
            }

            $result      = $metric;
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new body metric record for own user.
     *
     * @param  StoreBodyMetricRequest  $request
     * @return JsonResponse
     */
    public function store(StoreBodyMetricRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not create body metric.'];

        try {
            $data           = $request->validated();
            $data['user_id'] = $request->user()->id;
            $result          = BodyMetric::create($data);
            $messageArray    = ['general' => 'Body metric recorded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a body metric record. Admin only.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not delete body metric.'];

        try {
            BodyMetric::findOrFail($id)->delete();
            $result      = true;
            $messageArray = ['general' => 'Body metric deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
