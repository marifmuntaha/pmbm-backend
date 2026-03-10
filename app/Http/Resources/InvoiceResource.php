<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $details
 * @property mixed $personal
 * @property mixed $institution
 * @property mixed $reference
 * @property mixed $id
 * @property mixed $address
 * @property mixed $created_at
 * @property mixed $user
 * @property mixed $duedate
 * @property mixed $status
 * @property mixed $dueDate
 */
class InvoiceResource extends JsonResource
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
            if ($request->type == 'detail') {
                $resources = [
                    'id' => $this->id,
                    'institutionLogo' => $this->institution->logo,
                    'invoiceReference' => $this->reference,
                    'studentName' => $this->personal->name,
                    'studentAddress' => $this->address->street,
                    'invoiceCreated' => $this->created_at,
                    'userPhone' => $this->user->phone,
                    'invoiceDueDate' => $this->dueDate,
                    'invoiceDetails' => $this->details,
                    'invoiceStatus' => $this->status,
                    'invoicePayments' => $this->payments
                ];
            }
        }
        
        $resources['student_name'] = $this->whenLoaded('personal', function () {
            return $this->personal->name ?? '-';
        });

        $resources['original_amount'] = $this->whenLoaded('payments', function () {
            // Original amount is remaining balance + total paid
            return $this->amount + $this->payments->sum('amount');
        }, $this->amount); // Default to current amount if payments not loaded

        return $resources;
    }
}
