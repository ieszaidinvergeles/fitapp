<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a UserFavorite model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a favourite entry.
 * DIP: Consumers depend on this resource contract, not on the raw UserFavorite model.
 */
class UserFavoriteResource extends JsonResource
{
    /**
     * Transforms the UserFavorite model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id'     => $this->user_id,
            'entity_type' => $this->entity_type,
            'entity_id'   => $this->entity_id,
        ];
    }
}
