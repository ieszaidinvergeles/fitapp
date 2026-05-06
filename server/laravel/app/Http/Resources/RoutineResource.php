<?php

namespace App\Http\Resources;

use App\Models\UserFavorite;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Routine model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a routine.
 * DIP: Consumers depend on this resource contract, not on the raw Routine model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class RoutineResource extends JsonResource
{
    /**
     * Transforms the Routine model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'name'                     => $this->name,
            'description'              => $this->description,
            'difficulty_level'         => $this->difficulty_level,
            'estimated_duration_min'   => $this->estimated_duration_min,
            'cover_image_url'          => $this->cover_image_url
                                            ? route('routines.image', ['id' => $this->id])
                                            : null,
            'is_favorite'              => $this->is_favorite_flag ?? false,
            'creator_id'               => $this->creator_id,
            'associated_diet_plan_id'  => $this->associated_diet_plan_id,
            'exercises_count'          => $this->exercises_count ?? 0,
            'creator'                  => new UserResource($this->whenLoaded('creator')),
            'exercises'                => ExerciseResource::collection(
                $this->whenLoaded('orderedExercises') ?? $this->whenLoaded('exercises')
            ),
        ];
    }
}
