<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a BodyMetric model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of body metrics.
 * DIP: Consumers depend on this resource contract, not on the raw BodyMetric model.
 */
class BodyMetricResource extends JsonResource
{
    /**
     * Transforms the BodyMetric model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'user_id'          => $this->user_id,
            'date'             => $this->date?->toDateString(),
            'weight_kg'        => $this->weight_kg,
            'height_cm'        => $this->height_cm,
            'body_fat_pct'     => $this->body_fat_pct,
            'muscle_mass_pct'  => $this->muscle_mass_pct,
            'bmi'              => $this->weight_kg && $this->height_cm 
                                    ? round($this->weight_kg / (($this->height_cm/100) ** 2), 1) 
                                    : null,
        ];
    }
}
