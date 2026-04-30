<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Room model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a room.
 * DIP: Consumers depend on this resource contract, not on the raw Room model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class RoomResource extends JsonResource
{
    /**
     * Transforms the Room model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'gym_id'    => $this->gym_id,
            'name'      => $this->name,
            'capacity'  => $this->capacity,
            'image_url' => $this->image_url
                            ? route('rooms.image', ['id' => $this->id])
                            : null,
            'gym'       => new GymResource($this->whenLoaded('gym')),
        ];
    }
}
