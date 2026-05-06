<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a GymClass model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a gym class.
 * DIP: Consumers depend on this resource contract, not on the raw GymClass model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class GymClassResource extends JsonResource
{
    /**
     * Transforms the GymClass model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'gym_id'         => $this->gym_id,
            'activity_id'    => $this->activity_id,
            'instructor_id'  => $this->instructor_id,
            'room_id'        => $this->room_id,
            'start_time'     => $this->start_time?->toIso8601String(),
            'end_time'       => $this->end_time?->toIso8601String(),
            'capacity_limit' => $this->capacity_limit,
            'bookings_count' => (int) ($this->bookings_count ?? $this->bookings()->where('status', 'active')->count()),
            'is_full'        => (bool) ($this->capacity_limit > 0 && ($this->bookings_count ?? $this->bookings()->where('status', 'active')->count()) >= $this->capacity_limit),
            'is_cancelled'   => $this->is_cancelled,
            'gym'            => new GymResource($this->whenLoaded('gym')),
            'activity'       => new ActivityResource($this->whenLoaded('activity')),
            'instructor'     => new UserResource($this->whenLoaded('instructor')),
            'room'           => new RoomResource($this->whenLoaded('room')),
        ];
    }
}
