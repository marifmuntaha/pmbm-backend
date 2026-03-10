<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $institution
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $resources =  parent::toArray($request);
        if ($request->has('with')) {
            if ($request->with === 'institution') {
                $resources['institution'] = $this->institution;
            }
        }
        if ($request->has('type')) {
            if ($request->type === 'select') {
                $resources = [
                    'value' => $this->id,
                    'label' => $this->name
                ];
            }
        }
        return $resources;
    }
}
