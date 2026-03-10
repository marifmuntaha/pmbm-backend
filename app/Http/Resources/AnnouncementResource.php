<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnnouncementResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'yearId'        => $this->yearId,
            'institutionId' => $this->institutionId,
            'user_id'       => $this->user_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'type'          => $this->type,
            'is_wa_sent'    => $this->is_wa_sent,
            'createdBy'     => $this->createdBy,
            'updatedBy'     => $this->updatedBy,
            'created_at'    => $this->created_at,
            'updated_at'    => $this->updated_at,
        ];
    }
}
