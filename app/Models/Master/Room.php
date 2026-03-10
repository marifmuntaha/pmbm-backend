<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $table = 'master_rooms';
    protected $fillable = [
        'id',
        'name',
        'capacity',
        'createdBy',
        'updatedBy',
    ];
}
