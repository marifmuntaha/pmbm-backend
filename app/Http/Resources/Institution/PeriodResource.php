<?php

namespace App\Http\Resources\Institution;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class PeriodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resources = [
            'id' => $this->id,
            'yearId' => $this->yearId,
            'institutionId' => $this->institutionId,
            'name' => $this->name,
            'description' => $this->description,
            'start' => $this->start,
            'end' => $this->end,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ];

        if ($request->has('type')) {
            if ($request->type == 'select') {
                $resources = [
                    'label' => $this->name . " (" . Carbon::parse($this->start)->translatedFormat('d F Y') . " - " . Carbon::parse($this->end)->translatedFormat('d F Y') . ")",
                    'value' => $this->id,
                ];
            }
        }
        return $resources;
    }
}
