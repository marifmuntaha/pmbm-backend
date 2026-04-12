<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $institution
 */
class WhatsappResource extends JsonResource
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
                $resources['institution'] = $this->institution;
            }
        }
        return $resources;
    }
}
