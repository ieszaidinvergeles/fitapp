<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Http\Resources\SettingResource;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles read and update operations for user settings.
 *
 * SRP: Solely responsible for handling HTTP requests related to user settings.
 * DIP: Delegates authorization decisions to SettingPolicy via the Gate contract.
 */
class SettingController extends Controller
{
    /**
     * Returns the settings record for the authenticated user.
     * Access is restricted to the record owner (enforced by SettingPolicy).
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not retrieve settings.'];

        try {
            $setting = Setting::where('user_id', $request->user()->id)->firstOrFail();
            $this->authorize('view', $setting);

            $result       = new SettingResource($setting);
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates the settings record for the authenticated user.
     * Access is restricted to the record owner (enforced by SettingPolicy).
     *
     * @param  UpdateSettingRequest  $request
     * @return JsonResponse
     */
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $result       = false;
        $messageArray = ['general' => 'Could not update settings.'];

        try {
            $setting = Setting::where('user_id', $request->user()->id)->firstOrFail();
            $this->authorize('update', $setting);

            $result       = $setting->update($request->validated());
            $messageArray = ['general' => 'Settings updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
