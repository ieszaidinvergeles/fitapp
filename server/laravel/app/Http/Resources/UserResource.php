<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a User model into a standardized, sanitized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a user.
 * DIP: Consumers depend on this resource contract, not on the raw User model.
 *
 * Sensitive fields excluded: password_hash, remember_token.
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class UserResource extends JsonResource
{
    /**
     * Transforms the User model into a sanitized array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'username'                 => $this->username,
            'full_name'                => $this->full_name,
            'email'                    => $this->email,
            'role'                     => $this->role,
            'dni'                      => $this->dni,
            'birth_date'               => $this->birth_date?->toDateString(),
            'profile_photo_url'        => $this->profile_photo_url
                                            ? '/uploads/' . $this->profile_photo_url
                                            : null,
            'membership_status'        => $this->membership_status,
            'cancellation_strikes'     => $this->cancellation_strikes,
            'is_blocked_from_booking'  => $this->is_blocked_from_booking,
            'current_gym_id'           => $this->current_gym_id,
            'membership_plan_id'       => $this->membership_plan_id,
            'gym'                      => new GymResource($this->whenLoaded('currentGym')),
            'membership_plan'          => new MembershipPlanResource($this->whenLoaded('membershipPlan')),
        ];
    }
}
