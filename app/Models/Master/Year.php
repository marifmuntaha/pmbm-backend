<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Year extends Model
{
    protected $table = 'master_years';
    protected $fillable = [
        'id',
        'name',
        'description',
        'active',
        'createdBy',
        'updatedBy',
    ];

    protected $casts = [
        'active' => 'integer',
    ];
}
