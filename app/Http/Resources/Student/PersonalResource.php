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
                if ($request->data == "transaction") {
                    $resources = [
                        'value' => $this->id,
                        'label' => $this->name,
                        'data' => [
                            'student' => [
                                'name' => $this->name,
                                'number' => $this->studentProgram?->registration_number,
                            ],
                            'parent' => [
                                'id' => $this->studentParent?->id,
                                'guardName' => $this->studentParent?->guardName,
                            ],
                            'address' => [
                                'id' => $this->studentAddress?->id,
                                'street' => $this->studentAddress?->street
                            ],
                            'program' => [
                                'id' => $this->studentProgram?->id,
                                'institution' => $this->studentProgram->institution->surname,
                                'name' => $this->studentProgram->program->name
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
        }
        return $resources;
    }
}
