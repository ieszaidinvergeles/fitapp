<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a UserMealSchedule model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a meal schedule entry.
 * DIP: Consumers depend on this resource contract, not on the raw UserMealSchedule model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class UserMealScheduleResource extends JsonResource
{
    /**
     * Transforms the UserMealSchedule model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'user_id'     => $this->user_id,
            'date'        => $this->date?->toDateString(),
            'meal_type'   => $this->meal_type,
            'recipe_id'   => $this->recipe_id,
            'is_consumed' => $this->is_consumed,
            'recipe'      => $this->recipe_id ? \App\Models\Recipe::find($this->recipe_id) : null,
        ];
    }
}
