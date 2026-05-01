<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $program
 * @property mixed $boarding
 * @property mixed $programId
 * @property mixed $boardingId
 */
class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resources =  parent::toArray($request);

        if ($request->has('type')) {
            if ($request->type == 'select') {
                $price = number_format($this->price);
                $resources = [
                    'value' => $this->id,
                    'label' => "$this->name {$this->program?->name} {$this->boarding?->name} $price",
                    'data' => [
                        "item" => [
                            "id" => $this->id,
                            "name" => $this->name,
                            "price" => $this->price
                        ],
                    ],
                ];
            }
        }
        if ($request->has('list')) {
            if ($request->list === 'table') {
                if ($this->programId !== 0) {
                    $resources['program'] = ['name' => optional($this->program)->name];
                }
                if ($this->isBoarding == 1 && $this->boardingId != 0) {
                    $resources['boarding'] = ['name' => optional($this->boarding)->name];
                }
            }
        }
        return $resources;
    }
}
