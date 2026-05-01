<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalResource extends JsonResource
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
            if ($request->type == 'select') {
                $resources = [
                    'value' => $this->id,
                    'label' => "{$this->studentProgram->registration_number} $this->name ({$this->studentParent->guardName})",
                    'data' => [
                        'student' => [
                            'name' => $this->name,
                            'number' => $this->studentProgram?->registration_number,
                        ],
                        'invoice' => [
                            'id' => $this->invoice?->id,
                            'amount' => $this->invoice?->amount,
                            'status' => $this->invoice?->status
                        ]
                    ],
                ];
            }
        }
        return $resources;
    }
}
