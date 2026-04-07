<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a StaffAttendance model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of an attendance record.
 * DIP: Consumers depend on this resource contract, not on the raw StaffAttendance model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class StaffAttendanceResource extends JsonResource
{
    /**
     * Transforms the StaffAttendance model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'staff_id'  => $this->staff_id,
            'gym_id'    => $this->gym_id,
            'date'      => $this->date?->toDateString(),
            'clock_in'  => $this->clock_in?->toIso8601String(),
            'clock_out' => $this->clock_out?->toIso8601String(),
            'staff'     => new UserResource($this->whenLoaded('staff')),
            'gym'       => new GymResource($this->whenLoaded('gym')),
        ];
    }
}
