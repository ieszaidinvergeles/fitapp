<?php

namespace App\Http\Resources;

use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RecipeResource;

/**
 * Transforms a DietPlan model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a diet plan.
 * DIP: Consumers depend on this resource contract, not on the raw DietPlan model.
 */
class DietPlanResource extends JsonResource
{
    /**
     * Transforms the DietPlan model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'goal_description' => $this->goal_description,
            'cover_image_url'  => $this->cover_image_url
                                    ? url('uploads/' . $this->cover_image_url)
                                    : null,
            'is_favorite'      => $this->is_favorite_flag ?? false,
            'recipes'          => RecipeResource::collection($this->whenLoaded('recipes')),
        ];
    }
}
