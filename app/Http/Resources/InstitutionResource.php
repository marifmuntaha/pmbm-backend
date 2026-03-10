<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $id
 * @property mixed $name
 * @property mixed $surname
 * @property mixed $tagline
 * @property mixed $npsn
 * @property mixed $nsm
 * @property mixed $address
 * @property mixed $phone
 * @property mixed $email
 * @property mixed $website
 * @property mixed $head
 * @property mixed $logo
 * @property mixed $createdBy
 * @property mixed $updatedBy
 * @property mixed $activities
 * @property mixed $students
 * @property mixed $programs
 */
class InstitutionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resource = [
            'id' => $this->id,
            'name' => $this->name,
            'surname' => $this->surname,
            'tagline' => $this->tagline,
            'npsn' => $this->npsn,
            'nsm' => $this->nsm,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'head' => $this->head,
            'logo' => $this->logo,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ];
        if ($request->has('page')) {
            if ($request->page == 'landing') {
                $resource['activity'] = $this->activities->where('yearId', $request->yearId)?->first();
                $resource['students'] = $this->students->where('yearId', $request->yearId)->select(['id', 'institutionId', 'boarding'])->toArray();
                $resource['programs'] = $this->programs->where('yearId', $request->yearId)?->toArray();
            }
        } else if ($request->has('type')) {
            if ($request->type == 'select') {
                $resource = ['value' => $this->id, 'label' => $this->surname];
            }
        }
        return $resource;
    }
}
