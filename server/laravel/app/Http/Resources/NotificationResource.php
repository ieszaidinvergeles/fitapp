<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a Notification model into a standardized API JSON response.
 *
 * SRP: Solely responsible for shaping the public-facing representation of a notification.
 * DIP: Consumers depend on this resource contract, not on the raw Notification model.
 *
 * Related models are conditionally loaded via whenLoaded() to prevent N+1 queries.
 */
class NotificationResource extends JsonResource
{
    /**
     * Transforms the Notification model into an array representation.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'sender_id'       => $this->sender_id,
            'title'           => $this->title,
            'body'            => $this->body,
            'target_audience' => $this->target_audience,
            'related_gym_id'  => $this->related_gym_id,
            'created_at'      => $this->created_at?->toIso8601String(),
            'sender'          => new UserResource($this->whenLoaded('sender')),
        ];
    }
}
