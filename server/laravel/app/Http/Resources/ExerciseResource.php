<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms an Exercise model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of an exercise.
 * DIP: Consumers depend on this resource contract, not on the raw Exercise model.
 */
class ExerciseResource extends JsonResource
{
    /**
     * Transforms the Exercise model into an array representation.
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
            'target_muscle_group' => $this->target_muscle_group,
            'image_url'           => $this->image_url,
            'video_url'           => $this->video_url,
        ];
    }
}
