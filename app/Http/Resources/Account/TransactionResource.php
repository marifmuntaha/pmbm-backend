<?php

namespace App\Http\Resources\Account;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            if ($request->type === 'datatable') {
                $resources = [
                    'id' => $this->id,
                    'account' => [
                        'id' => $this->account->id,
                        'name' => $this->account->name
                    ],
                    'name' => $this->name,
                    'credit' => $this->credit,
                    'debit' => $this->debit,
                    'balance' => $this->balance
                ];
            }
        }


        return $resources;
    }
}
