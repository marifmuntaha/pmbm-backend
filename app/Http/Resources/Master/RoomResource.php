<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoomResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
