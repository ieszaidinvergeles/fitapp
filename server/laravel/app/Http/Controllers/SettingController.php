<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Handles read and update operations for user settings.
 *
 * SRP: Solely responsible for handling HTTP requests related to user settings.
 */
class SettingController extends Controller
{
    /**
     * Returns the settings for the authenticated user. Own or admin.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function show(Request $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not retrieve settings.'];

        try {
            $result      = Setting::where('user_id', $request->user()->id)->firstOrFail();
            $messageArray = ['general' => 'OK'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }

    /**
     * Updates the settings for the authenticated user. Own user only.
     *
     * @param  UpdateSettingRequest  $request
     * @return JsonResponse
     */
    public function update(UpdateSettingRequest $request): JsonResponse
    {
        $result      = false;
        $messageArray = ['general' => 'Could not update settings.'];

        try {
            $setting     = Setting::where('user_id', $request->user()->id)->firstOrFail();
            $result      = $setting->update($request->validated());
            $messageArray = ['general' => 'Settings updated.'];
        } catch (\Exception $e) {
            $messageArray = ['general' => $e->getMessage()];
        }

        return response()->json(['result' => $result, 'message' => $messageArray]);
    }
}
