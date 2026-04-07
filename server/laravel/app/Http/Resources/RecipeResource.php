<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Recipe model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a recipe.
 * DIP: Consumers depend on this resource contract, not on the raw Recipe model.
 *
 * The macros_json field is already cast to array in the model; it is exposed as-is.
 */
class RecipeResource extends JsonResource
{
    /**
     * Transforms the Recipe model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'type'        => $this->type,
            'calories'    => $this->calories,
            'macros'      => $this->macros_json,
            'image_url'   => $this->image_url,
        ];
    }
}
