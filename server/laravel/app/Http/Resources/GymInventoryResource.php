<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GymInventoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->gym_id . '-' . $this->equipment_id,
            'gym_id'       => $this->gym_id,
            'equipment_id' => $this->equipment_id,
            'quantity'     => $this->quantity,
            'status'       => $this->status,
            'gym'          => new GymResource($this->whenLoaded('gym')),
            'equipment'    => new EquipmentResource($this->whenLoaded('equipment')),
        ];
    }
}
