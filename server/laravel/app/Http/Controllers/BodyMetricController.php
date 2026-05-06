<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBodyMetricRequest;
use App\Http\Resources\BodyMetricResource;
use App\Models\BodyMetric;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles CRUD operations for body metric records.
 *
 * SRP: Solely responsible for handling HTTP requests related to body metrics.
 * DIP: Delegates authorization decisions to BodyMetricPolicy via the Gate contract.
 */
class BodyMetricController extends Controller
{
    /**
     * Returns a paginated list of body metrics.
     * Admins see all records; standard users see only their own (scoped by Policy).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve body metrics.'];

        try {
            $this->authorize('viewAny', BodyMetric::class);

            $query = ($request->user()->isAdmin()
                ? BodyMetric::query()
                : BodyMetric::where('user_id', $request->user()->id))
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc');

            $result       = BodyMetricResource::collection($query->paginate(10)->withQueryString());
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Returns a single body metric record.
     * Access is restricted to the owner or an admin (enforced by BodyMetricPolicy).
     *
     * @param  Request  $request
     * @param  int      $id
     * @return JsonResponse
     */
    public function show(Request $request, int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not retrieve body metric.'];

        try {
            $metric = BodyMetric::findOrFail($id);
            $this->authorize('view', $metric);

            $result       = new BodyMetricResource($metric);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Creates a new body metric record for the authenticated user.
     *
     * @param  StoreBodyMetricRequest  $request
     * @return JsonResponse
     */
    public function store(StoreBodyMetricRequest $request): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not create body metric.'];

        try {
            $this->authorize('create', BodyMetric::class);

            $data            = $request->validated();
            $data['user_id'] = $request->user()->id;
            $result          = new BodyMetricResource(BodyMetric::create($data));
            $messageArray    = ['general' => 'Body metric recorded.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Deletes a body metric record. Admin only (enforced by BodyMetricPolicy + Gate::before).
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        /** @var mixed $result */        $result       = false;
        $messageArray = ['general' => 'Could not delete body metric.'];

        try {
            $metric = BodyMetric::findOrFail($id);
            $this->authorize('delete', $metric);

            $metric->delete();
            $result       = true;
            $messageArray = ['general' => 'Body metric deleted.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
