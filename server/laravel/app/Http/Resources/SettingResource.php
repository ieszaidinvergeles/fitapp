<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Setting model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of user settings.
 * DIP: Consumers depend on this resource contract, not on the raw Setting model.
 */
class SettingResource extends JsonResource
{
    /**
     * Transforms the Setting model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id'               => $this->user_id,
            'language_preference'   => $this->language_preference,
            'theme_preference'      => $this->theme_preference,
            'share_workout_stats'   => $this->share_workout_stats,
            'share_body_metrics'    => $this->share_body_metrics,
            'share_attendance'      => $this->share_attendance,
        ];
    }
}
