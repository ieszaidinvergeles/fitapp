<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Activity model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of an activity.
 * DIP: Consumers depend on this resource contract, not on the raw Activity model.
 */
class ActivityResource extends JsonResource
{
    /**
     * Transforms the Activity model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'description'     => $this->description,
            'intensity_level' => $this->intensity_level,
            'image_url'       => $this->image_url,
        ];
    }
}
