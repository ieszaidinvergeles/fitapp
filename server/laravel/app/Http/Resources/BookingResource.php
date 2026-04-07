<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Booking model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a booking.
 * DIP: Consumers depend on this resource contract, not on the raw Booking model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class BookingResource extends JsonResource
{
    /**
     * Transforms the Booking model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'class_id'     => $this->class_id,
            'user_id'      => $this->user_id,
            'status'       => $this->status,
            'booked_at'    => $this->booked_at?->toIso8601String(),
            'cancelled_at' => $this->cancelled_at?->toIso8601String(),
            'gym_class'    => new GymClassResource($this->whenLoaded('gymClass')),
            'user'         => new UserResource($this->whenLoaded('user')),
        ];
    }
}
