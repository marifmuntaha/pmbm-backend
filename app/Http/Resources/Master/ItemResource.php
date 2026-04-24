<?php

namespace App\Http\Resources\Master;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resources = parent::toArray($request);
        if ($request->has('type')) {
            if ($request->type == 'report') {
                $programStr = $this->program ? " ({$this->program->name})" : "";
                $genderStr = match ((int)$this->gender) {
                    1 => " (Laki-laki)",
                    2 => " (Perempuan)",
                    default => "",
                };
                $resources = [
                    'id' => $this->id,
                    'name' => "{$this->name}{$programStr}{$genderStr}",
                    'invoice' => $this->invoice->map(function ($item) {
                        return $item->details->where('productId', $this->id)->sum('price');
                    })->sum(),
                    'discount' => $this->invoice->map(function ($item) {
                        return $item->details->where('productId', $this->id)->sum('discount');
                    })->sum()
                ];
            }
        }
        return $resources;
    }
}
