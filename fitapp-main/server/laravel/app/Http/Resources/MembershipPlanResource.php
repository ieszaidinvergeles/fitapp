<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a MembershipPlan model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a membership plan.
 * DIP: Consumers depend on this resource contract, not on the raw MembershipPlan model.
 */
class MembershipPlanResource extends JsonResource
{
    /**
     * Transforms the MembershipPlan model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                 => $this->id,
            'name'               => $this->name,
            'type'               => $this->type,
            'price'              => $this->price,
            'allow_partner_link' => $this->allow_partner_link,
            'badge_image_url'    => $this->badge_image_url
                                        ? route('membership-plans.image', ['id' => $this->id])
                                        : null,
        ];
    }
}
