<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Gym model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a gym.
 * DIP: Consumers depend on this resource contract, not on the raw Gym model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class GymResource extends JsonResource
{
    /**
     * Transforms the Gym model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'address'         => $this->address,
            'city'            => $this->city,
            'location_coords' => $this->location_coords,
            'phone'           => $this->phone,
            'logo_url'        => $this->logo_url
                                    ? route('gyms.logo', ['id' => $this->id])
                                    : null,
            'manager_id'      => $this->manager_id,
            'manager'         => new UserResource($this->whenLoaded('manager')),
        ];
    }
}
