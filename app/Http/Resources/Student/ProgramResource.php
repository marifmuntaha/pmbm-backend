<?php

namespace App\Http\Resources\Student;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property mixed $institution
 * @property mixed $period
 * @property mixed $program
 * @property mixed $boarding
 * @property mixed $personal
 */
class ProgramResource extends JsonResource
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
            $with = explode(',', $request->with);
            if (in_array('institution', $with)) {
                $resources['institution'] = $this->institution;
            }
            if (in_array('period', $with)) {
                $resources['period'] = $this->period;
            }
            if (in_array('program', $with)) {
                $resources['program'] = $this->program;
            }
            if (in_array('boarding', $with)) {
                $resources['boarding'] = $this->boarding;
            }
            if (in_array('personal', $with)) {
                $resources['personal'] = $this->personal;
            }
            if (in_array('room', $with)) {
                $resources['room'] = $this->room;
            }

        }
        return $resources;
    }
}
