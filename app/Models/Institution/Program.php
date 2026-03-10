<?php

namespace App\Models\Institution;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $table = 'institution_programs';
    protected $fillable = [
        'id',
        'yearId',
        'institutionId',
        'name',
        'alias',
        'description',
        'boarding',
        'createdBy',
        'updatedBy'
    ];
}
