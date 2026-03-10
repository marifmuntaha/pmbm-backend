<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Rule extends Model
{
    protected $fillable = [
        'institutionId',
        'content',
        'createdBy',
        'updatedBy',
    ];

    public function institution()
    {
        return $this->belongsTo(\App\Models\Institution::class, 'institutionId');
    }
}
