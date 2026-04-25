<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
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
            if ($request->type === 'datatable') {
                $resources = [
                    'id' => $this->id,
                    'userId' => $this->userId,
                    'yearId' => $this->yearId,
                    'institutionId' => $this->institutionId,
                    'invoiceId' => $this->invoiceId,
                    'name' => $this->personal?->name,
                    'reference' => $this->invoice?->reference,
                    'method' => $this->method,
                    'status' => $this->status,
                    'transaction_id' => $this->transaction_id,
                    'transaction_time' => $this->transaction_time,
                    'amount' => $this->amount,
                    'deposited' => $this->deposited,
                    'created_at' => $this->created_at,
                ];
            }
        }
        return $resources;
    }
}
