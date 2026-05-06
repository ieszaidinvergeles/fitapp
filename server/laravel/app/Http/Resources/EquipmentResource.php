<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Equipment model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of an equipment item.
 * DIP: Consumers depend on this resource contract, not on the raw Equipment model.
 */
class EquipmentResource extends JsonResource
{
    /**
     * Transforms the Equipment model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'name'                => $this->name,
            'description'         => $this->description,
            'image_url'           => $this->image_url
                                        ? \url('uploads/' . $this->image_url)
                                        : null,
            'is_home_accessible'  => $this->is_home_accessible,
        ];
    }
}
