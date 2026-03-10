<?php

namespace App\Http\Resources\Invoice;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'invoiceId' => $this->invoiceId,
            'productId' => $this->productId,
            'name' => $this->name,
            'price' => $this->price,
            'discount' => $this->discount,
            'amount' => $this->amount,
        ];
    }
}
