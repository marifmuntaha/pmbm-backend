<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Boarding extends Model
{
    protected $table = 'master_boardings';
    protected $fillable = [
        'id',
        'name',
        'surname',
        'description',
        'createdBy',
        'updatedBy',
    ];
}
