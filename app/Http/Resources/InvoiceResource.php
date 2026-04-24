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
            if ($request->type == 'datatable') {
                $resources = [
                    'id' => $this->id,
                    'reference' => $this->reference,
                    'student_name' => $this->whenLoaded('personal', function () {
                        return $this->personal->name ?? '-';
                    }),
                    'program_name' => $this->whenLoaded('program', function () {
                        return $this->program->program->name ?? '-';
                    }),
                    'original_invoice' => $this->whenLoaded('details', function () {
                        return $this->details->where('invoiceId', $this->id)->sum('price');
                    }),
                    'discount' => $this->whenLoaded('details', function () {
                        return $this->details->where('invoiceId', $this->id)->sum('discount');
                    }),
                    'original_amount' => $this->whenLoaded('payments', function () {
                        return $this->amount + $this->payments->sum('amount');
                    }),
                    'payment' => $this->whenLoaded('payments', function () {
                        return  $this->payments->sum('amount');
                    }),
                    'unpaid' => $this->whenLoaded('payments', function () {
                        return  $this->amount;}),
                    'status' => $this->status,
                ];
            }
        }

        return $resources;
    }
}
