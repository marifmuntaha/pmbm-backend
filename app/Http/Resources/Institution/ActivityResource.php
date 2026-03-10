<?php

namespace App\Http\Resources\Institution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $yearId
 * @property mixed $institutionId
 * @property mixed $capacity
 * @property mixed $brochure
 * @property mixed $createdBy
 * @property mixed $updatedBy
 * @property mixed $year
 */
class ActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id' => $this->id,
            'yearId' => $this->yearId,
            'institutionId' => $this->institutionId,
            'capacity' => $this->capacity,
            'brochure' => $this->brochure,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy
        ];
        if ($request->has('list')) {
            if ($request->list == 'table') {
                $resource = [
                    'id' => $this->id,
                    'yearId' => $this->yearId,
                    'institutionId' => $this->institutionId,
                    'capacity' => $this->capacity,
                    'brochure' => $this->brochure,
                    'year' => $this->year
                ];
            }
        }
        return $resource;
    }
}
