<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'goal'        => $this->goal,
        ];
    }
}
